1) Добавить `api.alfacash.local` в хосты
2) В `env.local` проставить креды от **Binance API** 
3) `docker-compose -f docker-compose.traefik.yml up -d`
4) `cd backend && composer install && npm install` **(npm нужен для того, чтобы RoadRunner обновлял код)**
5) `cd ../ && docker-compose up -d`
6) После запуска должен появиться файл `backend/rr`
7) Даем ему разрешение `sudo chmod 765 backend/rr && docker-compose up -d`
8) После запуска зайти в контейнер и запустить билдинг графа `php artisan exchanges:update-graph binance`
9) Теперь можно проверять `api.alfacash.local/exchange/best-rate/XEM_ETH/30000`
