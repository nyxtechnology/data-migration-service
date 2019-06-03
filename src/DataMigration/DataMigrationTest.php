<?php

namespace DataMigration;
require "DataMigration.php";


use DataMigration\DataMigration;

class DataMigrationTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }


    /**
     * Testing create the Header with auth.
     */
    public function testCreateHeader() {
        $dataMigration = new DataMigration();
        $result = $this->invokeMethod($dataMigration, 'createHeader', [
            'get',
            \DataMigration\DataMigration::$NO_AUTH,
        ]);

        $this->assertNotContains('Authorization: Basic', $result);

        $result = $this->invokeMethod($dataMigration, 'createHeader', [
            'get',
            \DataMigration\DataMigration::$BASIC_AUTH,
            'keybasic'
        ]);

        $this->assertContains('Authorization: Basic keybasic', $result);
    }

    /**
     * @dataProvider fromToProvider
     */
    public function testFromTo($startObject, $toObject, $migrationSettings) {

        $dataMigration = new DataMigration();

        $result = $dataMigration->fromTo($startObject->from, $migrationSettings);

        $this->assertEquals($result, $toObject);

    }

    public function fromToProvider() {

        return [
            [
                json_decode('{"from":{
                "name":"user",
                "last_name":"last"
                }}'),

                json_decode('{
                "user_name":"user",
                "last_username":"last",
                "time":"now"
                }'),

                json_decode('{
                    "user_name":"@name",
                    "last_username":"@last_name",
                    "time":"now"
                }'),
            ],
            [
                json_decode('{"from":{
                "user":{
                    "name":"user",
                    "last_name":"last"
                    }
                }}'),

                json_decode('{
                "user_name":"user",
                "last_username":"last",
                "time":"now"
                }'),

                json_decode('{
                    "user_name":"@user.name",
                    "last_username":"@user.last_name",
                    "time":"now"
                }'),
            ],
            [
                json_decode('{"from":{
                "data_sources": {
                    "products": [
                            {"name":"Product 1", "id":"123"},
                            {"name":"Product 2", "id":"1234"}
                        ]
                },
                "user":{
                    "name":"user",
                    "last_name":"last"
                    }
                }}'),

                json_decode('{
                "user_name":"user",
                "last_username":"last",
                "time":"now",
                "buy":"Product 1"
                }'),

                json_decode('{
                    "user_name":"@user.name",
                    "last_username":"@user.last_name",
                    "time":"now",
                    "buy":"@data_sources.products[id=123].name"
                }'),
            ],
        ];
    }
}