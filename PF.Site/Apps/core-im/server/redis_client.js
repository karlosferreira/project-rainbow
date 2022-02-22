var config = require('./config.js')
var redisClient = require('redis').createClient(config.redis)

module.exports = { redisClient }
