## Пример проекта на компонентах Symfony

Проект имитирует API магазина для заказа и оплаты товаров. Основные используемые компоненты:

* symfony/dependency-injection
* symfony/http-kernel
* symfony/routing
* doctrine/orm
* doctrine/migrations
* symfony/serializer

Для база данных используется SQLite, файл которой хранится в *./storage/db.sqlite*. При желании адрес можно поменять в *./config/parameters.php*, как и другие настройки проекта. 

### Запуск

Необходимо подтянуть зависимости, накатить миграции и поднять Docker-контейнер:

```
$ composer install
$ ./vendor/bin/doctrine migrations:migrate
$ docker-compose up -d
```

После этого сайт доступен по адресу http://localhost:8081.

### Использование

Доступны следующие методы:

**Список товаров**

[GET] localhost:8081/products

**Генерация товаров**

[POST] localhost:8081/products/generate

**Список заказов**

[GET] localhost:8081/orders

**Создание заказа**

[POST] localhost:8081/orders?products_id[]={product1}&products_id[]={product2}...

* products_id – массив ID продктов, котоыпе должны попасть в заказ. Они должны существовать.

**Оплата заказа**

[POST] localhost:8081/orders/{order_id}/payment?amount={amount}

* order_id – ID существующего заказа, который должен быть ещё не оплачен.
* amount – сумма оплаты, которая должна совпадать с суммой заказа.