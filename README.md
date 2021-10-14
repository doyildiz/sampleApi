1 - Fill the database settings in .env file, and set QUEUE_CONNECTION, SESSION_DRIVER, CACHE_DRIVER as redis.
2 - php artisan migrate
-- It creates database tables
3 - Run the "php artisan queue:work" command
4 - Run the "php artisan save:data" command
-- It saves sample data from json files to database



-- APP_URL/api/list
It returns all orders with order details. 

-- APP_URL/api/delete/:orderId
It gets order ID parameter, and deletes that order with order details.

-- APP_URL/api/delete/save
It accepts post request parameters. And creates a new order with these parameters.
Sample parameter set:

customer_id:2
items[0][productId]:3
items[0][quantity]:5
items[1][productId]:100
items[1][quantity]:7


-- APP_URL/api/discount/:orderId
It calculates discount for that order.

-- Laravel Sail (https://laravel.com/docs/8.x/sail) is used for creating docker compose file.
