var request = require('request')
var Config = require('./config')
var redis = require('./redis_client').redisClient
var emojione = require('emojione/lib/js/emojione')
var DEBUG = false
var FirebaseConfigStorage = {}

function setFirebaseConfig(options) {
  if (DEBUG) {
    console.log({
      operation: 'setFirebaseConfig',
      options: options
    })
  }
  redis.set(options.prefix + 'firebaseServerKey', options.serverKey, function (error, result) {
    if (error && DEBUG) {
      console.log({error: error, result: result})
    }
  })
  redis.set(options.prefix + 'firebaseSenderId', options.senderId, function (error, result) {
    if (error && DEBUG) {
      console.log({error: error, result: result})
    }
  })
}

function getFirebaseConfig(prefix) {
  var key = prefix + 'firebaseServerKey'
  var id = prefix + 'firebaseSenderId'

  return new Promise(function (resolve, reject) {
    if (!Config.is_hosted) {
      if (Config.firebase) {
        resolve(Config.firebase)
      } else {
        resolve(Object.assign({serverKey: null, senderId: null}))
      }
    }
    else {
      redis.get(key, function (error, response) {
        FirebaseConfigStorage[key] = response
        redis.get(id, function (error, response) {
          FirebaseConfigStorage[id] = response
          resolve({
            _senderId: id,
            _serverKey: key,
            serverKey: FirebaseConfigStorage[key],
            senderId: FirebaseConfigStorage[id],
            prefix: prefix
          })
        })
      })
    }
  })
}

function getNotificationKey(options, notificationTokenName) {
  return new Promise(function (resolve, reject) {
    request({
      url: 'https://fcm.googleapis.com/fcm/notification',
      method: 'GET',
      json: true,
      headers: {
        Authorization: 'key=' + options.serverKey,
        project_id: options.senderId,
        'Content-Type': 'application/json'
      },
      qs: {notification_key_name: notificationTokenName}
    }, function (error, httpResponse, body) {
      if (!error && body.notification_key) {
        resolve(body.notification_key)
      } else {
        reject(error || body)
      }
    })
  })
}

function pushNotification(prefix, payload, badge) {
  return getFirebaseConfig(prefix).then(function (options) {
    if (DEBUG) {
      console.log({
        options,
        payload
      })
    }

    if (!options || !options.senderId || !options.serverKey) {
      return
    }

    if (!payload.user || !payload.receiver || !payload.receiver.id || !payload.user.id ||
      !payload.user.name) {
      return
    }

    var notificationTokenName = 'user-' + payload.receiver.id

    return getNotificationKey(options, notificationTokenName).then(function (notificationKey) {
      request({
        url: 'https://fcm.googleapis.com/fcm/send',
        method: 'POST',
        json: true,
        headers: {
          Authorization: 'key=' + options.serverKey,
          'Content-Type': 'application/json'
        },
        body: {
          to: notificationKey,
          priority: 'high',
          notification: {
            title: payload.user.name,
            body: emojione.shortnameToUnicode(payload.text),
            // vibrate: true,
            badge: badge,
            sound: 'default',
            click_action: ''
          },
          data: {
            resource_link: 'chat/' + payload.user.id,
            web_link: 'chat/' + payload.user.id
          }
        }
      }, function (error, httpResponse, body) {
        if (!DEBUG) {
          console.log({
            error: error,
            body: body
          })
        }
      })
    }).catch(function (error) {
      if (DEBUG) {
        console.log(error)
      }
    })
  }).catch(function (error) {
    if (DEBUG) {
      console.log(error)
    }
  })
}

module.exports = {
  pushNotification,
  setFirebaseConfig
}
