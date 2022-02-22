# Poll API

Restful API for Web Version

## Restful API document

### 1. GET /poll ###

*Browse polls*

*Example uri: http://example.com/restful_api/poll?search[search]=christmas&when=this-month*

*Parameters:*

    - search[search]: Keyword for searching

    - user_id: Browse by owner

    - sort: Support sort return results, available options: latest, most-liked, most-viewed, most-talked

    - when: Browse polls by available options: all-time, this-month, this-week, today

    - limit: Limit return results

    - page: Paging return results

    - item_id: Support browse polls on item (pages/groups)

    - module_id: Support search polls on item (pages/groups)

    - view: Support some view modes: my, pending 

*Response:*
```json
  {
      "status": "success",
      "data": {
        "file": 776,
        "type": "blog",
        "field_name": "temp_file"
      },
      "message": "",
      "error": null
  }
  ```

### 2. GET /poll/:id ###
*Get poll detail*

*Example uri: http://example.com/restful_api/poll/4*

*Parameters:*

 - id: poll id
   
*Response:*
  ```json
  {
      "status": "success",
      "data": {
        "user_id": "1",
        "poll_id": "4",
        "module_id": null,
        "item_id": "0",
        "view_id": "0",
        "question": "Where are you from ?",
        "description": "<p>Where are you from ?<\/p>",
        "description_parsed": "<p>Where are you from ?<\/p>",
        "privacy": "0",
        "image_path": "http:\/\/core.local\/PF.Base\/file\/pic\/poll\/2021\/08\/6911dfe1587067cb963ae0daadb1d176_500.png",
        "time_stamp": "1618902394",
        "total_comment": "0",
        "total_like": "0",
        "total_view": "1",
        "total_attachment": "0",
        "randomize": "0",
        "hide_vote": "0",
        "is_featured": "0",
        "is_sponsor": "0",
        "is_multiple": "0",
        "close_time": "0",
        "answer": [
          {
            "answer_id": "13",
            "poll_id": "4",
            "answer": "England",
            "total_votes": "0",
            "ordering": "1",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          },
          {
            "answer_id": "14",
            "poll_id": "4",
            "answer": "China",
            "total_votes": "0",
            "ordering": "2",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          },
          {
            "answer_id": "15",
            "poll_id": "4",
            "answer": "Australia",
            "total_votes": "0",
            "ordering": "3",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          },
          {
            "answer_id": "16",
            "poll_id": "4",
            "answer": "US",
            "total_votes": "0",
            "ordering": "4",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          }
        ]
      },
      "message": "",
      "error": null
  }
  ```
  
### 3. POST /poll ###
*Create a Poll*

*Example uri: http://example.com/restful_api/poll*

*Parameters:*

    - val[question] - string - required - Title of Poll

    - val[description] - string - no required - Poll description

    - val[temp_file] - int - no required - value of file_id in table temp_file

    - val[answer][1][answer] - string - required - Answer title

    - val[hide_vote] - int (1 | 0) - no required - Hide voting or not

    - val[is_multiple] - int (1 | 0) - no required - Allow to multiple choices

    - val[enable_close] - int (1 | 0) - no required - Enable closed time for Poll

    - val[close_month] - int - required if you set value for enable_close is 1 - Month for closed time

    - val[close_day] - int - required if you set value for enable_close is 1 - Day for closed time

    - val[close_year] - int - required if you set value for enable_close is 1 - Year for closed time

    - val[close_hour] - int - required if you set value for enable_close is 1 - Hour for closed time

    - val[close_minute] - int - required if you set value for enable_close is 1 - Minute for closed time

    - val[privacy] - int - no required - Poll privacy

*Response:*
  ```json
  {
      "status": "success",
      "data": {
        "user_id": "1",
        "poll_id": "4",
        "module_id": null,
        "item_id": "0",
        "view_id": "0",
        "question": "Where are you from ?",
        "description": "<p>Where are you from ?<\/p>",
        "description_parsed": "<p>Where are you from ?<\/p>",
        "privacy": "0",
        "image_path": "http:\/\/core.local\/PF.Base\/file\/pic\/poll\/2021\/08\/6911dfe1587067cb963ae0daadb1d176_500.png",
        "time_stamp": "1618902394",
        "total_comment": "0",
        "total_like": "0",
        "total_view": "1",
        "total_attachment": "0",
        "randomize": "0",
        "hide_vote": "0",
        "is_featured": "0",
        "is_sponsor": "0",
        "is_multiple": "0",
        "close_time": "0",
        "answer": [
          {
            "answer_id": "13",
            "poll_id": "4",
            "answer": "England",
            "total_votes": "0",
            "ordering": "1",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          },
          {
            "answer_id": "14",
            "poll_id": "4",
            "answer": "China",
            "total_votes": "0",
            "ordering": "2",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          },
          {
            "answer_id": "15",
            "poll_id": "4",
            "answer": "Australia",
            "total_votes": "0",
            "ordering": "3",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          },
          {
            "answer_id": "16",
            "poll_id": "4",
            "answer": "US",
            "total_votes": "0",
            "ordering": "4",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          }
        ]
      },
      "message": "Poll successfully added.",
      "error": null
  }
  ```

### 4. PUT /poll/:id ###

*Update a Poll*

*Example uri: http://example.com/restful_api/poll/4*

*Parameters:*

    - val[question] - string - required - Title of Poll

    - val[description] - string - no required - Poll description

    - val[temp_file] - int - no required - value of file_id in table temp_file

    - val[answer][1][answer] - string - required - Answer title

    - val[hide_vote] - int (1 | 0) - no required - Hide voting or not

    - val[is_multiple] - int (1 | 0) - no required - Allow to multiple choices

    - val[enable_close] - int (1 | 0) - no required - Enable closed time for Poll

    - val[close_month] - int - required if you set value for enable_close is 1 - Month for closed time

    - val[close_day] - int - required if you set value for enable_close is 1 - Day for closed time

    - val[close_year] - int - required if you set value for enable_close is 1 - Year for closed time

    - val[close_hour] - int - required if you set value for enable_close is 1 - Hour for closed time

    - val[close_minute] - int - required if you set value for enable_close is 1 - Minute for closed time

    - val[privacy] - int - no required - Poll privacy

    - val[remove_photo] - int (1 | 0) - no reauired - Notice that current photo was removed by user

*Response:*
  ```json
  {
      "status": "success",
      "data": {
        "user_id": "1",
        "poll_id": "4",
        "module_id": null,
        "item_id": "0",
        "view_id": "0",
        "question": "Where are you from ?",
        "description": "<p>Where are you from ?<\/p>",
        "description_parsed": "<p>Where are you from ?<\/p>",
        "privacy": "0",
        "image_path": "http:\/\/core.local\/PF.Base\/file\/pic\/poll\/2021\/08\/6911dfe1587067cb963ae0daadb1d176_500.png",
        "time_stamp": "1618902394",
        "total_comment": "0",
        "total_like": "0",
        "total_view": "1",
        "total_attachment": "0",
        "randomize": "0",
        "hide_vote": "0",
        "is_featured": "0",
        "is_sponsor": "0",
        "is_multiple": "0",
        "close_time": "0",
        "answer": [
          {
            "answer_id": "13",
            "poll_id": "4",
            "answer": "England",
            "total_votes": "0",
            "ordering": "1",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          },
          {
            "answer_id": "14",
            "poll_id": "4",
            "answer": "China",
            "total_votes": "0",
            "ordering": "2",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          },
          {
            "answer_id": "15",
            "poll_id": "4",
            "answer": "Australia",
            "total_votes": "0",
            "ordering": "3",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          },
          {
            "answer_id": "16",
            "poll_id": "4",
            "answer": "US",
            "total_votes": "0",
            "ordering": "4",
            "voted": null,
            "vote_percentage": 0,
            "some_votes": []
          }
        ]
      },
      "message": "Poll successfully updated.",
      "error": null
  }
  ```

### 5. DETELE /poll/:id

*Delete poll*

*Example uri: http://example.com/restful_api/poll/4*

*Parameters:*

    - id: poll id

*Response:*
  ```json
  {
      "status": "success",
      "data": [],
      "message": "Poll successfully deleted.",
      "error": null
  }
  ```