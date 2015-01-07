Mouf Memcached cache service
============================

This package contains an implementation of Mouf's CacheInterface for Memcache, using the **memcached** pecl package.
To learn more about the cache interface, please see the [cache system documentation](http://mouf-php.com/packages/mouf/utils.cache.cache-interface).

This package comes with a default installer that will create a "memcacheCacheService" instance that points
to a Memcache server on 127.0.0.1 listening on port 11211.

To use it:

```php
Mouf::getMemcacheCacheService()->set('mykey', 'myvalue');

$mykey = Mouf::getMemcacheCacheService()->get('mykey');
```