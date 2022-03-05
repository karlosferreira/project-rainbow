// Load required libs
var config = require('./config.js'),
  server,
  fs = require('fs'),
  md5 = require('md5')
var redis = require('./redis_client').redisClient
var pushNotification = require('./fcm').pushNotification
var URL = require('url')
var setFirebaseConfig = require('./fcm').setFirebaseConfig
var isDebug = false

if (config.hasOwnProperty('secure') && config.secure) {
  if (!fs.existsSync(config.privateKey)) {
    throw new Error('privateKey not found.')
  }
  if (!fs.existsSync(config.cert)) {
    throw new Error('cert not found.')
  }
  server = require('https').Server({
    key: fs.readFileSync(config.privateKey),
    cert: fs.readFileSync(config.cert)
  })
} else {
  server = require('http').Server()
}

var io = require('socket.io')(server),
  path = require('path'),
  home_path = path.dirname(process.mainModule.filename) + '/'


server.listen(config.port, function () {
})

// Create needed variables
var friends = {},
  thread = {},
  default_number = 20,
  remain = 0,
  total = 0,
  thread_index = 0,
  thread_length = 0

  PF = {
    config: config,
    event: {
      hooks: {},

      on: function (name, callback) {
        if (typeof (this.hooks[name]) == 'undefined') {
          this.hooks[name] = []
        }
        this.hooks[name].push(callback)
      },

      trigger: function (name, params) {
        if (typeof (this.hooks[name]) == 'object') {
          for (var i in this.hooks[name]) {
            this.hooks[name][i](params)
          }
        }
      }
    },
    max_connection: 100,
    connection: {},

    verifyHost: function (socket) {
      console.log('VERIFY HOST')

      var host = ''
      if (typeof socket.handshake.headers.origin !== 'undefined' &&
        socket.handshake.headers.origin !== '') {
        host = socket.handshake.headers.origin
      } else if (typeof socket.handshake.headers.im_host !== 'undefined' &&
        socket.handshake.headers.im_host !== '') {
        host = socket.handshake.headers.im_host
      }

      if (!host && config.is_hosted) {
        PF.connectFailed(socket)
      }

      if (host === '') {
        PF.connectFailed(socket)
      }
      console.log('VERIFY HOST: ok')

      return host
    },

    connectSuccesfully: function (socket) {
      console.log('VERIFY CLIENT:', 'successfully')
      socket.emit('connect_successfully')
      PF.event.trigger('init_socket_events', socket)
    },

    connectFailed: function (socket, message, allowRetry) {
      console.log('VERIFY CLIENT:', 'failed')

      // bring failed message
      if (typeof message === 'undefined') {
        message = 'Unable to connect to the IM server.'
      }

      // emit retry
      var date = new Date()
      date.setHours(0, 0, 0, 0)
      var timestamp = (date.valueOf() - date.getTimezoneOffset() * 60000) / 1000,
        params = {
          message: message,
          im_timestamp: timestamp,
          retry: typeof allowRetry === 'boolean' && allowRetry &&
          typeof socket.handshake.query.retry === 'undefined'
        }

      // tell client to retry connection
      socket.emit('retry', params)
      // send error message
      socket.emit('host_failed', message)
      // disconnect connection
      socket.disconnect()
    }
  }

// Load any custom hooks
require('fs').readdirSync(home_path + 'hooks/').forEach(function (file) {
  require(home_path + 'hooks/' + file)(PF)
})

server.on('request', function (req, res) {
  var parsed = URL.parse(req.url, true)
  var query = parsed.query
  var pathname = parsed.pathname
  var senderId = query.senderId
  var serverKey = query.serverKey
  var prefix = query.host + '@'

  if (senderId && serverKey && query.host && prefix) {
    setFirebaseConfig({
      senderId,
      serverKey,
      prefix
    })
    res.writeHead(200, {
      "Content-Type": "text/plain"
    })
    res.end()
  }
})

// Start the connection
io.on('connection', function (socket) {
  console.log('onConnection')
  socket.initEvents = false
  var host = PF.verifyHost(socket)
  // get domain from url
  host = require('url').parse(host).hostname

  if (typeof socket.handshake.query.host !== 'undefined') {
    host = socket.handshake.query.host
  }
  socket.prefix = config.is_hosted ? host + '@' : ''
  console.log('connect with ' + socket.prefix)
  PF.event.trigger('socket_connection', {
    socket: socket,
    redis: redis,
    host: host,
    token: (typeof socket.handshake.query.token !== 'undefined')
      ? socket.handshake.query.token
      : ''
  })

  PF.event.on('init_socket_events', function (socket) {
    if (socket.initEvents) {
      return
    }

    socket.initEvents = true

    // Hide a thread
    socket.on('hideThread', function (data) {
      redis.set([socket.prefix + 'thread:hide:' + data.id + ':' + data.user_id, 1])
    })

    // delete user
    socket.on('deleteUser', function (user_id) {
      redis.del([socket.prefix + 'user/' + user_id])
    })


    // Load all threads
    socket.on('loadAllThreads', function () {
      if (isDebug) {
        console.log('On loadThreads: Loading all threads')
      }
      redis.keys(socket.prefix + 'thread:*', function (err, threads) {
        var list_threads = []
        threads.forEach(function (id, index) {
          if (id.indexOf('hide:') === -1) {
            list_threads.push(id)
          }
        })
        list_threads.forEach(function (id, index) {
          var thread_id = id.replace(socket.prefix + 'thread:', ''), last_item = index === list_threads.length - 1;
          var users = thread_id.split(':'), thread_data = {}
          redis.zrange([socket.prefix + 'message:' + thread_id, 0, -1], function (err, messages) {
            thread_data = {
              thread_id: thread_id,
              users: users,
              messages: messages,
              last_thread: last_item,
              hidden: {}
            }
            users.forEach(function ( user_id, index) {
              redis.get(socket.prefix + 'thread:hide:' + thread_id + ':' + user_id, function (err, hidden) {
                if (hidden === null) {
                  thread_data.hidden[user_id] = false
                } else {
                  thread_data.hidden[user_id] = true
                }
                if (index) {
                  socket.emit('loadAllThreads', thread_data, thread_id)
                }
              })
            })
          })
        })
      })
    })

    // Load all threads for user
    socket.on('loadThreads', function (user_id, pf_total_conversations, start_index) {
      if (isDebug) {
        console.log('On loadThreads: Loading threads for: ' + user_id)
      }
      remain = total = parseInt(pf_total_conversations)
      thread_index = start_index || 0

      //Get threads length
      redis.llen(socket.prefix + 'threads:' + user_id, function (err, length) {
        thread_length = length
      });

      get_thread(user_id, parseInt(start_index || 0))
    })

    function get_thread(user_id, start) {
      redis.lrange([socket.prefix + 'threads:' + user_id, start, remain + start - 1],
        function (err, threads) {
          if (threads.length === 0) {
            if (isDebug) {
              console.log('1. socket emit lastThread')
            }
            socket.emit('lastThread')
            return
          }
          if (isDebug) {
            console.log(threads)
          }
          for (var i in threads) {
            get_thread_closure1(i, threads[i], threads.length, user_id)()
          }
        })
    }

    function get_thread_closure1(i, thread, length, user_id) {
      return function () {
        redis.get(socket.prefix + 'thread:' + thread, function (err, thread) {
          thread = JSON.parse(thread)
          redis.zrange([socket.prefix + 'message:' + thread.thread_id, -1, -1],
            function (err, result) {
              if (typeof (result[0]) !== 'undefined') {
                var message = JSON.parse(result[0])
                thread.is_deleted = message.deleted
              }
            })

          redis.get(socket.prefix + 'new:message:' + thread.thread_id + ':' + user_id,
            function (err, is_new) {
              thread.is_new = is_new
              get_thread_closure2(i, thread, length, user_id)()
            })
        })
      }
    }

    function get_thread_closure2(i, thread, length, user_id) {
      return function () {
        redis.get(socket.prefix + 'thread:hide:' + thread.thread_id + ':' + user_id,
          function (err, is_hidden) {
            thread_index++
            if (is_hidden === null) {
              remain--
              thread.is_hidden = is_hidden
              socket.emit('loadThreads', JSON.stringify(thread))
            } else {
              // emit hidden thread to exclude it on getting friends
              socket.emit('hiddenThread', thread.thread_id)
            }
            // check for last thread
            if (i == length - 1) {
              if (remain > 0) {
                get_thread(user_id, thread_index)
              }
              if (remain === 0 || total === 0) {
                socket.emit('lastThread', thread, thread_length)
              }
            }
          })
      }
    }

    socket.on('loadConversation', function (conversation) {
      if (!conversation.ignore_notify) {
        redis.get(socket.prefix + 'message:notification:' + conversation.user_id,
          function (err, result) {
            var listUnread = (result === null) ? [] : JSON.parse(result)
            if (listUnread.indexOf(conversation.thread_id) > -1) {
              listUnread.splice(listUnread.indexOf(conversation.thread_id), 1)
            }
            redis.set([socket.prefix + 'message:notification:' + conversation.user_id, JSON.stringify(listUnread)])
          })
      }
      redis.get(socket.prefix + 'thread:' + conversation.thread_id,
        function (err, thread) {
          if (thread === null) {
            socket.emit('loadNewConversation', conversation, conversation.thread_id)
            return
          }

          thread = JSON.parse(thread)
          if (!conversation.ignore_notify) {
            redis.del(socket.prefix + 'new:message:' + thread.thread_id + ':' +
              conversation.partner_id)
          }
          redis.zrange(
            [socket.prefix + 'message:' + thread.thread_id, -default_number, -1],
            function (e, messages) {
              socket.emit('loadConversation', messages, conversation.thread_id)
            })
          if (typeof thread.notification !== 'undefined') {
            var notification = thread.notification.split(':')
            socket.emit('loadNotification', notification.indexOf(
              conversation.user_id.toString()) !== -1, conversation.thread_id)
          } else {
            // support old data
            socket.emit('loadNotification', true)
          }
          socket.broadcast.emit(socket.prefix + 'resetCounterAndTitle',
            conversation.user_id, thread.thread_id)
          socket.broadcast.emit('http://' + socket.prefix + 'resetCounterAndTitle',
            conversation.user_id, thread.thread_id)
          socket.broadcast.emit('https://' + socket.prefix + 'resetCounterAndTitle',
            conversation.user_id, thread.thread_id)
        })
    })

    socket.on('loadSearchPreview', function (params) {
      var getLastMessage = function (thread_id) {
        redis.zrange([socket.prefix + 'message:' + thread_id, -1, -1],
          function (e, message) {
            if (message.length > 0) {
              message = JSON.parse(message[0])
              socket.emit('loadSearchPreview', {
                'text': message.text,
                'thread_id': message.thread_id,
                'deleted': message.deleted
              })
            }
          })
      }
      getLastMessage(params.friend_id + ':' + params.user_id)
      getLastMessage(params.user_id + ':' + params.friend_id)
    })

    // Delete a message
    socket.on('chat_delete', function (id, key) {
      redis.zrangebyscore([socket.prefix + 'message:' + id, key, key],
        function (err, result) {
          var message = JSON.parse(result[0])
          message.deleted = true
          redis.zremrangebyscore([socket.prefix + 'message:' + id, key, '(' + (key + 1)],
            function (err, result) {
              redis.zadd([socket.prefix + 'message:' + id, key, JSON.stringify(message)],
                function (err, result) {
                  io.sockets.emit('chat_delete', key, id)
                })
            })
        })
    })

    // Delete all messages
    socket.on('delete_all', function () {
      // remove threads
      redis.keys([socket.prefix + 'threads:*'],
        function (err, keys) {
          if (keys.length) {
            for (var i in keys) {
              redis.del([keys[i]])
            }
          }
        })
      // remove thread
      redis.keys([socket.prefix + 'thread:*'],
        function (err, keys) {
          if (keys.length) {
            for (var i in keys) {
              redis.del([keys[i]])
            }
          }
        })
      // remove message
      redis.keys([socket.prefix + 'message:*'],
        function (err, keys) {
          if (keys.length) {
            for (var i in keys) {
              redis.del([keys[i]])
            }
          }
        })
      // remove new:message
      redis.keys([socket.prefix + 'new:message:*'],
        function (err, keys) {
          if (keys.length) {
            for (var i in keys) {
              redis.del([keys[i]])
            }
          }
        })
      // remove message:notification
      redis.keys([socket.prefix + 'message:notification:*'],
        function (err, keys) {
          if (keys.length) {
            for (var i in keys) {
              redis.del([keys[i]])
            }
          }
        })
      socket.emit('delete_all')
    })

    // Add a new message to the thread
    socket.on('chat', function (chat) {

      redis.get(socket.prefix + 'message:notification:'+ chat.receiver.id,
        function (err, result) {
          var listUnread = (result === null) ? [] : JSON.parse(result);
          if (listUnread.indexOf(chat.thread_id) < 0) {
            listUnread.push(chat.thread_id)
          }
          pushNotification(socket.prefix, chat, listUnread.length)
          redis.set([socket.prefix + 'message:notification:' + chat.receiver.id, JSON.stringify(listUnread)])
      })

      var add_chat = function (thread, chat) {
        thread.preview = chat.text
        thread.updated = chat.time_stamp
        redis.set([socket.prefix + 'thread:' + chat.thread_id, JSON.stringify(thread)])

        // update new message counter
        redis.get(socket.prefix + 'new:message:' + chat.thread_id + ':' + chat.user.id,
          function (err, result) {
            result = (result === null) ? 1 : parseInt(result) + 1
            redis.set([
              socket.prefix + 'new:message:' + chat.thread_id + ':' + chat.user.id,
              result])
          })

        redis.zadd([
          socket.prefix + 'message:' + chat.thread_id + '',
          chat.time_stamp,
          JSON.stringify(chat)], function (err, result) {
          var users = chat.thread_id.split(':')
          for (var i in users) {
            var u = users[i]
            redis.lrem([socket.prefix + 'threads:' + u, 0, chat.thread_id],
              function (err, result) {

              })

            redis.lpush([socket.prefix + 'threads:' + u, chat.thread_id],
              function (err, result) {

              })

            redis.del(socket.prefix + 'thread:hide:' + chat.thread_id + ':' + u)
          }
          chat.notification = thread.notification
          redis.get(socket.prefix + 'new:message:' + chat.thread_id + ':' + chat.user.id,
            function (err, result) {
              chat.new = result
              socket.broadcast.emit(socket.prefix + 'chat', chat)
              socket.broadcast.emit('http://' + socket.prefix + 'chat', chat)
              socket.broadcast.emit('https://' + socket.prefix + 'chat', chat)
            })
        })
      }

      redis.get(socket.prefix + 'thread:' + chat.thread_id, function (err, thread) {
        if (thread === null) {
          var users = chat.thread_id.split(':')

          thread = {
            thread_id: chat.thread_id,
            listing_id: chat.listing_id,
            created: chat.time_stamp,
            users: users,
            preview: null,
            updated: null,
            notification: chat.thread_id
          }

          redis.set([socket.prefix + 'thread:' + chat.thread_id, JSON.stringify(thread)],
            function (err, result) {
              add_chat(thread, chat)
            })
        } else {
          add_chat(JSON.parse(thread), chat)
        }
      })
    })

    socket.on('loadMore', function (thread_id, number) {
      redis.get(socket.prefix + 'thread:' + thread_id, function (err, thread) {
        if (thread === null) {
          return
        }
        redis.zrange([
          socket.prefix + 'message:' + thread_id,
          -default_number - number,
          -1 - number], function (e, messages) {
          socket.emit('loadConversation', messages, thread_id)
        })
      })
    })

    socket.on('showThread', function (thread_id, u) {
      redis.del(socket.prefix + 'thread:hide:' + thread_id + ':' + u)
    })

    // toggle notification
    socket.on('toggleNoti', function (params) {
      redis.get(socket.prefix + 'thread:' + params.id, function (err, thread) {
        if (thread === null) {
          return
        }

        thread = JSON.parse(thread)
        // support old data
        if (typeof thread.notification === 'undefined') {
          thread.notification = thread.thread_id
        }

        var notification = thread.notification.split(':')
        if (params.noti) {
          notification.push(params.userId)
        } else if (notification.indexOf(params.userId.toString()) !== -1) {
          notification.splice(notification.indexOf(params.userId.toString()), 1)
        }
        thread.notification = notification.join(':')
        redis.set([socket.prefix + 'thread:' + params.id, JSON.stringify(thread)],
          function () {
          })
      })
    })

    socket.on('search_message', function (thread_id, text, index) {
      redis.zrange([socket.prefix + 'message:' + thread_id, 0, -1],
        function (err, messages) {
          var result = []
          messages.reverse()
          for (var i = 0; i < messages.length; i++) {
            var message = JSON.parse(messages[i])
            if (message.text.toLowerCase().indexOf(text.toLowerCase()) !== -1 &&
              (typeof message.deleted === 'undefined' || !message.deleted)) {
              result.push(messages[i])
            }
          }
          socket.emit('search_message',
            result.slice(index, index + default_number), index)
        })
    })

    socket.on('update_new', function (partner_id, thread_id, is_last) {
      redis.get(socket.prefix + 'new:message:' + thread_id + ':' + partner_id,
        function (err, result) {
          if (result !== null) {
            // check notification
            redis.get(socket.prefix + 'thread:' + thread_id, function (err, thread) {
              socket.emit('update_new', thread, result, is_last)
            })
          }
        })
    })

    socket.on('add_notification', function (thread_id) {
      redis.get(socket.prefix + 'thread:' + thread_id, function (err, thread) {
        thread = JSON.parse(thread)
        redis.del(socket.prefix + 'thread:' + thread.thread_id)
        thread.notification = thread.thread_id
        redis.set(
          [socket.prefix + 'thread:' + thread.thread_id, JSON.stringify(thread)])
      })
    })
  })
})
