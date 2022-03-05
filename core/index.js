require('dotenv').config();

const express = require('express')
const app = express()
const port = 3000

const fs = require('fs')

const Jimp = require('jimp') 

app.get('/', (req, res) => {
    (async function () {
        try {
          if (!fs.existsSync(process.env.RENDER_DIR + req.query.id + "/" + req.query.path)) {

        // default image
        const image = await Jimp.read(process.env.ROOT_DIR + req.query.path)

        const font = await Jimp.loadFont(Jimp.FONT_SANS_128_WHITE)

        image.print(font, 0, 0, req.query.id)            
            image.write(process.env.RENDER_DIR + req.query.id + "/" + req.query.path)
          }
        } catch(err) {
          console.log(err)
        }        

    })(res.status(200).sendFile(process.env.RENDER_DIR + req.query.id + "/" + req.query.path))
})

app.listen(port, () => {
  console.log(`Example app listening on port ${port}`)
})