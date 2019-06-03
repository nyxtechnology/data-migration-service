# Data Migration Service

This service allows you to migrate or copy data from one service to another.

You can also use it to migrate values between fields of the same service

## How to Use

Rename config.example.json to config.json

- Use `@<path variable>` to get value and replace to other service
- Use [] to filter data in array

Example:
Data From
```json
{
  "from":{
    "data_sources": {
        "products": [
                {"name":"Product 1", "id":"123"},
                {"name":"Product 2", "id":"1234"}
            ]
    },
    "user":{
        "name":"user",
        "last_name":"last",
        "product_id":"123"
        }
    }
}


```

Data you need

```json
{
    "user_name":"user",
    "last_username":"last",
    "time":"now",
    "buy":"Product 1"
}

```

Data settings to create your data migration

```json
{
    "user_name":"@user.name",
    "last_username":"@user.last_name",
    "time":"now",
    "buy":"@data_sources.products[id=@user->product_id].name"
}

```

Note: within [] use `->` instead of `.`