# Marketplace API

Restful API for Web Version

## Restful API document

### 1. GET / ###

*Browse items*

*Example uri: http://example.com/restful_api/marketplace?search[search]=christmas&when=this-month*

*Parameters:*

    - search[search]: Keyword for searching

    - user_id: Browse by owner

    - sort: Support sort return results, available options: latest, most-liked, most-talked

    - when: Browse items by available options: all-time, this-month, this-week, today

    - limit: Limit return results

    - page: Paging return results

    - item_id: Support browse items on item (pages/groups),

    - module_id: Support search items on item (pages/groups)

    - view: Support some view mode: my, pending, sold, featured, expired, invites

    - location: Browse by country iso with 2 alphabet

    - category_id: Browse by category

*Response:*
```json
  {
      "status": "success",
      "data": [
        {
          "user_id": "1",
          "country_iso": "US",
          "listing_id": "9",
          "view_id": "0",
          "privacy": "1",
          "module_id": "marketplace",
          "item_id": "0",
          "group_id": "0",
          "is_featured": "0",
          "is_sponsor": "0",
          "title": "Betta Fish",
          "currency_id": "USD",
          "price": "1.50",
          "country_child_id": "0",
          "postal_code": null,
          "city": null,
          "location": "Arizona, USA",
          "location_lat": "34.048928100000000000000000000",
          "location_lng": "-111.093731100000000000000000000",
          "time_stamp": "1628840963",
          "image_path": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/marketplace\/2021\/08\/e212f60dfd9d13dc55e83f9858e7ddc3_400_square.jpg",
          "total_comment": "0",
          "total_like": "0",
          "total_view": "1",
          "total_attachment": "0",
          "is_sell": "0",
          "allow_point_payment": "0",
          "is_closed": "0",
          "auto_sell": "0",
          "mini_description": "Betta Fish for selling",
          "is_notified": "0"
        }
      ],
      "message": "",
      "error": null
  }
  ```

### 2. GET /marketplace/:id ###

*Get marketplace detail*

*Example uri: http://example.com/restful_api/marketplace/1*

*Parameters:*

- id: marketplace id

*Response:*
  ```json
  {
    "status": "success",
    "data": {
        "user_id": "1",
        "country_iso": "US",
        "listing_id": "9",
        "view_id": "0",
        "privacy": "1",
        "module_id": "marketplace",
        "item_id": "0",
        "group_id": "0",
        "is_featured": "0",
        "is_sponsor": "0",
        "title": "Betta Fish",
        "currency_id": "USD",
        "price": "1.50",
        "country_child_id": "0",
        "postal_code": null,
        "city": null,
        "location": "Arizona, USA",
        "location_lat": "34.048928100000000000000000000",
        "location_lng": "-111.093731100000000000000000000",
        "time_stamp": "1628840963",
        "image_path": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/marketplace\/2021\/08\/e212f60dfd9d13dc55e83f9858e7ddc3_400_square.jpg",
        "total_comment": "0",
        "total_like": "0",
        "total_view": "1",
        "total_attachment": "0",
        "is_sell": "0",
        "allow_point_payment": "0",
        "is_closed": "0",
        "auto_sell": "0",
        "mini_description": "Betta Fish for selling",
        "is_notified": "0"
    },
    "message": "",
    "error": null
  }
  ```

### 3. POST /marketplace ###
*Create a marketplace*

*Example uri: http://example.com/restful_api/marketplace*

*Parameters:*

    - val[title] - string - required - Title of marketplace

    - val[category][] - int - required - Category of item

    - val[currency_id] - string - no required - Currency in 3 alphabet

    - val[price] - float - no required - Price

    - val[mini_description] - string - no required - Short description

    - val[description] - string - no required - description

    - val[is_sell] - int (1 | 0) - no required - Allow other users to pay this item

    - val[allow_point_payment] - int (1 | 0) - no required - Allow to pay with activity points

    - val[auto_sell] - int (1 | 0) - no required - Close this item after payment successfully once

    - val[location] - string - required - location of item

    - val[location_lat] - float - no required - lattitude of item

    - val[location_lng] - float - no required - longitude of item

    - val[country_iso] - string - no required - country iso in 2 alphabet

    - val[module_id] - string - no required - parent of item (pages/groups)

    - val[item_id] - int - no required - parent id of item

    - val[privacy] - int - no required - marketplace privacy

*Response:*
  ```json
  {
      "status": "success",
      "data": {
        "user_id": "1",
        "country_iso": "US",
        "listing_id": "9",
        "view_id": "0",
        "privacy": "1",
        "module_id": "marketplace",
        "item_id": "0",
        "group_id": "0",
        "is_featured": "0",
        "is_sponsor": "0",
        "title": "Betta Fish",
        "currency_id": "USD",
        "price": "1.50",
        "country_child_id": "0",
        "postal_code": null,
        "city": null,
        "location": "Arizona, USA",
        "location_lat": "34.048928100000000000000000000",
        "location_lng": "-111.093731100000000000000000000",
        "time_stamp": "1628840963",
        "image_path": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/marketplace\/2021\/08\/e212f60dfd9d13dc55e83f9858e7ddc3_400_square.jpg",
        "total_comment": "0",
        "total_like": "0",
        "total_view": "1",
        "total_attachment": "0",
        "is_sell": "0",
        "allow_point_payment": "0",
        "is_closed": "0",
        "auto_sell": "0",
        "mini_description": "Betta Fish for selling",
        "is_notified": "0"
      },
      "message": "Marketplace successfully added.",
      "error": null
  }
  ```

### 4. PUT /marketplace/:id ###

*Update a marketplace*

*Example uri: http://example.com/restful_api/marketplace/1*

*Parameters:*

    - val[title] - string - required - Title of marketplace

    - val[category][] - int - required - Category of item

    - val[currency_id] - string - no required - Currency in 3 alphabet

    - val[price] - float - no required - Price

    - val[mini_description] - string - no required - Short description

    - val[description] - string - no required - description

    - val[is_sell] - int (1 | 0) - no required - Allow other users to pay this item

    - val[allow_point_payment] - int (1 | 0) - no required - Allow to pay with activity points

    - val[auto_sell] - int (1 | 0) - no required - Close this item after payment successfully once

    - val[location] - string - required - location of item

    - val[location_lat] - float - no required - lattitude of item

    - val[location_lng] - float - no required - longitude of item

    - val[country_iso] - float - no required - country iso in 2 alphabet

    - val[module_id] - string - no required - parent of item (pages/groups)

    - val[item_id] - int - no required - parent id of item

    - val[privacy] - int - no required - marketplace privacy

    - val[view_id] - int (2) - no required - Mark item as sold

*Response:*
  ```json
  {
      "status": "success",
      "data": {
        "user_id": "1",
        "country_iso": "US",
        "listing_id": "9",
        "view_id": "0",
        "privacy": "1",
        "module_id": "marketplace",
        "item_id": "0",
        "group_id": "0",
        "is_featured": "0",
        "is_sponsor": "0",
        "title": "Betta Fish",
        "currency_id": "USD",
        "price": "1.50",
        "country_child_id": "0",
        "postal_code": null,
        "city": null,
        "location": "Arizona, USA",
        "location_lat": "34.048928100000000000000000000",
        "location_lng": "-111.093731100000000000000000000",
        "time_stamp": "1628840963",
        "image_path": "https:\/\/foxqc.sgp1.cdn.digitaloceanspaces.com\/file\/pic\/marketplace\/2021\/08\/e212f60dfd9d13dc55e83f9858e7ddc3_400_square.jpg",
        "total_comment": "0",
        "total_like": "0",
        "total_view": "1",
        "total_attachment": "0",
        "is_sell": "0",
        "allow_point_payment": "0",
        "is_closed": "0",
        "auto_sell": "0",
        "mini_description": "Betta Fish for selling",
        "is_notified": "0"
      },
      "message": "Marketplace successfully updated.",
      "error": null
  }
  ```

### 5. DETELE /marketplace/:id

*Delete marketplace*

*Example uri: http://example.com/restful_api/marketplace/1*

*Parameters:*

    - id: marketplace id

*Response:*
  ```json
  {
      "status": "success",
      "data": [],
      "message": "Marketplace successfully deleted.",
      "error": null
  }
  ```