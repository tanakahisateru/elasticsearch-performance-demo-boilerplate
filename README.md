# Elasticsearch performance demo

Initially add dummy data to **demo** index.

```
bin/console app:es-fill -a 10000
```

Then search by text.

```
bin/console app:es-query Alice  
{"query":{"bool":{"should":[{"match_phrase":{"message":"Alice"}},{"term":{"user":"Alice"}}],"minimum_should_match":1}},"sort":{"timestamp":"desc"}}
Took:7ms
Hits:5286
```

See `config/services.yml` and [DemoUseCase](src/Search/DemoUseCase.php) implementation to create your own use case.

```yml
services:

    App\Command\EsUseCaseInterface:
        class: 'App\Search\DemoUseCase'
        # class: 'App\Search\YourUseCase'
```
