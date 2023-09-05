Documentation can be seen after running the following commands:
```
$ php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
$ php artisan l5-swagger:generate
```


Now documentation can be found under '/api/documentation'

- please add a file named database.sqlite to the database folder for unit testing
