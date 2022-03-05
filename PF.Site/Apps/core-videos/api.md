# Video API

Restful API for Web Version

## Restful API document

### 1. GET / ###

*Browse items*

*Example uri: http://example.com/restful_api/video?search[search]=christmas&when=this-month*

*Parameters:*

    - search[search]: Keyword for searching

    - user_id: Browse by owner

    - sort: Support sort return results, available options: latest, most-liked, most-talked, most-talked

    - when: Browse items by available options: all-time, this-month, this-week, today

    - limit: Limit return results

    - page: Paging return results

    - item_id: Support browse items on item (pages/groups),

    - module_id: Support search items on item (pages/groups)

    - view: Support some view mode: my, pending, sold, featured, expired, invites

    - category_id: Browse by category

*Response:*
```json
  {
      "status": "success",
      "data": [
        {
          "user_id": "1",
          "embed_code": "\n\t\t\t\t<video data-resolution-x=\"320\" data-resolution-y=\"576\" data-src=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4\" data-poster=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_500.jpg\" data-video-id=\"61\"  width=\" 100%\" height=\"360\" controls \n\t\t\t\tclass=\"js-pf-video-embed\" poster=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_500.jpg\" preload=\"auto\" data-setup=\"{}\">\n\t\t\t\t    <source src=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4\" type=\"video\/mp4\">\n                <\/video>",
          "video_id": "61",
          "in_process": "0",
          "is_stream": "0",
          "is_featured": "0",
          "is_spotlight": "0",
          "is_sponsor": "0",
          "view_id": "0",
          "module_id": "video",
          "item_id": "0",
          "privacy": "0",
          "title": "This is uploading video",
          "parent_user_id": "0",
          "destination": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4",
          "file_ext": "mp4",
          "duration": "00:22",
          "resolution_x": "320",
          "resolution_y": "576",
          "image_path": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_500.jpg",
          "total_comment": "0",
          "total_like": "0",
          "time_stamp": "1630920454",
          "total_view": "0",
          "status_info": "",
          "page_user_id": "0",
          "location_latlng": null,
          "location_name": null,
          "tagged_friends": null
        }
      ],
      "message": "",
      "error": null
  }
  ```

### 2. GET /video/:id ###

*Get video detail*

*Example uri: http://example.com/restful_api/video/1*

*Parameters:*

- id: video id

*Response:*
  ```json
  {
      "status": "success",
      "data": {
        "video_id": "61",
        "in_process": "0",
        "is_stream": "0",
        "is_featured": "0",
        "is_spotlight": "0",
        "is_sponsor": "0",
        "view_id": "0",
        "module_id": "video",
        "item_id": "0",
        "privacy": "0",
        "title": "This is uploading video",
        "user_id": "1",
        "parent_user_id": "0",
        "destination": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4",
        "file_ext": "mp4",
        "duration": "22",
        "resolution_x": "320",
        "resolution_y": "576",
        "image_path": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_1024.jpg",
        "total_comment": "0",
        "total_like": "0",
        "time_stamp": "1630920454",
        "total_view": "0",
        "status_info": "",
        "page_user_id": "0",
        "location_latlng": null,
        "location_name": null,
        "tagged_friends": null,
        "embed_code": "\n\t\t\t\t<video data-resolution-x=\"320\" data-resolution-y=\"576\" data-src=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4\" data-poster=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_1024.jpg\" data-video-id=\"61\"  style=\"width: 100%; height: auto;\" width=\" 100%\" height=\"360\" controls \n\t\t\t\tclass=\"js-pf-video-embed\" poster=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_1024.jpg\" preload=\"auto\" data-setup=\"{}\">\n\t\t\t\t    <source src=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4\" type=\"video\/mp4\">\n                <\/video>"
      },
      "message": "",
      "error": null
  }
  ```

### 3. POST /video/file ###
*Prepare file for creating upload video*

*Example uri: http://example.com/restful_api/video/file*

*Parameters:*

    - file - file - Required - Video file for uploading

    - val[module_id] - string - Parent type of video (Etc: pages/groups)

    - val[item_id] - string - Parent id of video (Etc: pages/groups id)

*Response:*
  ```json
  {
      "status": "success",
      "data": {
        "pf_video_id": "7c28aecd3dd35dddc416283fc12ea306"
      },
      "message": "",
      "error": null
  }
  ```

### 3. POST /video ###
*Create a video*

*Example uri: http://example.com/restful_api/video*

*Parameters:*

    - val[pf_video_id] - string - required if using zencoder/ffmpeg for video upload - Zencoder/job id when pushing video into it

    - val[url] - string - required if upload video by url - url of video

    - val[is_feed] - boolean - required if you want to upload video via feed - flag to know upload in feed

    - val[status_info] - string - required if you want to upload video via feed - status of video in feed

    - val[title] - string - required - Title of Video

    - val[category][] - int - required - Category of item

    - val[text] - string - no required - description

    - val[module_id] - string - no required - parent of item (pages/groups)

    - val[item_id] - int - no required - parent id of item

    - val[privacy] - int - no required - marketplace privacy

*Response:*
  ```json
  {
      "status": "success",
      "data": {
        "video_id": "61",
        "in_process": "0",
        "is_stream": "0",
        "is_featured": "0",
        "is_spotlight": "0",
        "is_sponsor": "0",
        "view_id": "0",
        "module_id": "video",
        "item_id": "0",
        "privacy": "0",
        "title": "This is uploading video",
        "user_id": "1",
        "parent_user_id": "0",
        "destination": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4",
        "file_ext": "mp4",
        "duration": "22",
        "resolution_x": "320",
        "resolution_y": "576",
        "image_path": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_1024.jpg",
        "total_comment": "0",
        "total_like": "0",
        "time_stamp": "1630920454",
        "total_view": "0",
        "status_info": "",
        "page_user_id": "0",
        "location_latlng": null,
        "location_name": null,
        "tagged_friends": null,
        "embed_code": "\n\t\t\t\t<video data-resolution-x=\"320\" data-resolution-y=\"576\" data-src=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4\" data-poster=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_1024.jpg\" data-video-id=\"61\"  style=\"width: 100%; height: auto;\" width=\" 100%\" height=\"360\" controls \n\t\t\t\tclass=\"js-pf-video-embed\" poster=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_1024.jpg\" preload=\"auto\" data-setup=\"{}\">\n\t\t\t\t    <source src=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4\" type=\"video\/mp4\">\n                <\/video>"
      },
      "message": "Video successfully added.",
      "error": null
  }
  ```

### 4. PUT /video/:id ###

*Update a video*

*Example uri: http://example.com/restful_api/video/1*

*Parameters:*

    - val[title] - string - required - Title of marketplace

    - val[category][] - int - required - Category of item

    - val[text] - string - no required - description

    - val[privacy] - int - no required - marketplace privacy

    - val[temp_file] - int - no required - temp file id in table temp_file

    - val[remove_photo] - int - no required - remove current avatar of video    

*Response:*
  ```json
  {
      "status": "success",
      "data": {
        "video_id": "61",
        "in_process": "0",
        "is_stream": "0",
        "is_featured": "0",
        "is_spotlight": "0",
        "is_sponsor": "0",
        "view_id": "0",
        "module_id": "video",
        "item_id": "0",
        "privacy": "0",
        "title": "This is uploading video",
        "user_id": "1",
        "parent_user_id": "0",
        "destination": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4",
        "file_ext": "mp4",
        "duration": "22",
        "resolution_x": "320",
        "resolution_y": "576",
        "image_path": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_1024.jpg",
        "total_comment": "0",
        "total_like": "0",
        "time_stamp": "1630920454",
        "total_view": "0",
        "status_info": "",
        "page_user_id": "0",
        "location_latlng": null,
        "location_name": null,
        "tagged_friends": null,
        "embed_code": "\n\t\t\t\t<video data-resolution-x=\"320\" data-resolution-y=\"576\" data-src=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4\" data-poster=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_1024.jpg\" data-video-id=\"61\"  style=\"width: 100%; height: auto;\" width=\" 100%\" height=\"360\" controls \n\t\t\t\tclass=\"js-pf-video-embed\" poster=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/video\/2021\/09\/c5f7728a952b6c1592f522a1cef7b5ae_1024.jpg\" preload=\"auto\" data-setup=\"{}\">\n\t\t\t\t    <source src=\"https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/video\/2021\/09\/0ab9751346f7cfdc8d0af275dd978bbe.mp4\" type=\"video\/mp4\">\n                <\/video>"
      },
      "message": "Video successfully updated.",
      "error": null
  }
  ```

### 5. DETELE /video/:id

*Delete video*

*Example uri: http://example.com/restful_api/video/1*

*Parameters:*

    - id: video id

*Response:*
  ```json
  {
      "status": "success",
      "data": [],
      "message": "Video successfully deleted.",
      "error": null
  }
  ```