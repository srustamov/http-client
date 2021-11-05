# Php Http Client


## Requirements
- PHP >=**7.1**

## Installation
```$ composer require srustamov/http-client```

## Examples
```php 
<?php


use Srustamov\HttpClient\Http;

$response = Http::create()->get('https://jsonplaceholder.typicode.com/todos/1');

if ($response->isOk()) {
    // status code 200
}

var_dump($response->status());
var_dump($response->json());
var_dump($response['title']);


// set base url
$response = Http::create('https://jsonplaceholder.typicode.com')->post('/posts', [
    'title' => 'exmaple',
    'body' => 'post test body'
]);


var_dump($response->status());
var_dump($response->json());

$response = Http::create('https://jsonplaceholder.typicode.com')->put('/posts/1', [
    'title' => 'title change',
    'body' => 'post test change'
]);

if ($response->successful()) {
    print_r("post update successful\n");
}

var_dump($response->status());
var_dump($response->json());
var_dump($response['body']);

// Get Guzzle client response
//var_dump($response->getResponse());



// Headers And Authorization

$response = Http::create()
    //Add custom header
    ->addHeader('X-Author','Test')
    //Authorization : Bearer token
    ->bearer('token')
    //Accept : application/json
    ->acceptJson()
    //Content_type : application/json
    ->isJson()
    //Request timeout
    ->timeout(20)

    //Guzzle verify option set false
    ->withoutVerify()

    //Set Guzzle option
    //->setOption()
    
    ->when(true,function($http) {
       // condition code
    
    })
     
    ->post('any url',['any data']);



```
