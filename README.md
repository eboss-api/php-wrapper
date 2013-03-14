EBOSS Api PHP Wrapper
==================


Introduction
------------
This is a PHP wrapper class for the [EBOSS REST API](https://github.com/eboss-api/api-docs). Please refer to this link for any additional information about the underlying API methods.
It wraps the methods exposed via the REST API into php methods, takes care of caching and returns PHP objects, ready to render in your application. 

This allows suppliers who store their brand and product data with EBOSS to access and reuse this information for PHP website integration.


Access
------
Please [contact EBOSS](http://www.eboss.co.nz/contact) if you are an EBOSS supplier and would like access to the API.

Once approved EBOSS will supply you with an api username, api key, and brand id number.


Requirements
------------
PHP 5.3+
PHP-CURL extension or file_get_contents() allowed for urls


Authors
-------
Tim Klein, Dodat Ltd., tim[at]dodat[dot]co[dot]nz

David Craig, david[at]davidcraig[dot]co[dot]nz

Cam Findlay, Cam Findlay Consulting, info[at]camfindlay[dot]com


License
-------
This wrapper is released under the MIT License.

Copyright (c) 2013 EBOSS.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


Example
-------

    <?php
    $api_username = ""; //REPLACE WITH YOUR CREDENTIALS
    $api_key = ""; //REPLACE WITH YOUR CREDENTIALS
    $brand_id = XX; //REPLACE XX WITH YOUR BRANDID

    include("EbossAPIClient.php");

    $client = new EbossAPIClient($api_username, $api_key);
    
    $response = $client->Brand($brand_id);
    
    var_dump($response->Title);
    var_dump($response->Data());


For a full featured example, please refer to the [EBOSS php catalog application](https://github.com/eboss-api/php-catalog-app)


Available Methods
-----------------
Every API method returns an EbossAPIClient_Response object which represents the data requested. Variables can be directly called using the object's methods i.e. $response->Title, or alternatively debugged via the Data() method i.e. $response->Data().

**EbossAPIClient::Brand(int $brand_id)**

Returns information for $brand_id.


**EbossAPIClient::Categories(int $brand_id)**

Returns categories for $brand_id.


**EbossAPIClient::Category(int $brand_id , int $category_id)**

Returns category information for $brand_id and $category_id.


**EbossAPIClient::Ranges(int $brand_id)**

Returns ranges for $brand_id.


**EbossAPIClient::Range(int $brand_id , int $range_id)**

Returns range info for $brand_id and $range_id.


**EbossAPIClient::Products(int $brand_id [, array $filter])**

Returns products for $brand_id, filtered by $filter.

$filter should be an array with the following, optional key=>value pairs:

*CategoryID => $category_id*  - which returns all products within a category.

*RangeID => $range_id* - which returns all products within a range.


**EbossAPIClient::Product(int $brand_id , int $product_id)**

Returns the full list of all product information available for given $product_id within $brand_id scope.
