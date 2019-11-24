# Service_on_REST_architecture
Сервис реализует JSON API работающее по HTTP.
Каждой генерации присваивать уникальный id, по которому можно получить результат генерации методом retrieve.

Реализованные методы:
    POST /api/generate/ - генерация случайного значения и его идентификатора
	Запрос POST имеет следующие параметры:
	- type : ['int', 'string', 'guid', 'alphanumeric', 'setvalue'] - тип возвращаемого случайного значения;
	- len : [int] - длина возвращаемого значения от 1 до 49 (целое число);
	- setvalue : [string] - любые символы в любом количестве (работает только когда type=setvalue).

    GET /api/retrieve/ - получение значения по id, которое вернулось в методе generate
	Запрос GET имеет следующие параметры:
	- id : [int] - id получаемого значения (целое число).

Примеры запосов: 
	curl -X POST --data "type=int&len=20" http://127.0.0.1/api/generate/
	curl -H "Content-Type:application/json" --data '{"type":"setvalue","len":"20","setvalue":"2kkk22pp"}' http://127.0.0.1/api/generate/
	curl -H "Content-Type:application/json" http://127.0.0.1/api/retrieve/?id=1
	curl http://127.0.0.1/api/retrieve/?id=1

Сервис имеет возможность идемпотентных запросов.
Сервис поставляется в публичном Git-репозитории. Запуск сервисов производится с помощью инструмента docker-compose, из корня проекта.
