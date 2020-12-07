<?php

use PHPUnit\Framework\TestCase;
use Reliese\Coders\Model\Config;
use Reliese\Meta\Blueprint;

class ConfigTest extends TestCase
{
    /**
     * @dataProvider provideDataForTestGet
     *
     * @param array $config
     * @param string $key
     * @param string|array|bool|int|float $expected
     */
    public function testGet($config, $key, $expected)
    {
        $config = new Config($config);

        $baseBlueprint = Mockery::mock(Blueprint::class);
        $baseBlueprint->shouldReceive('schema')->andReturn('test');
        $baseBlueprint->shouldReceive('qualifiedTable')->andReturn('test.my_table');
        $baseBlueprint->shouldReceive('connection')->andReturn('test_connection');
        $baseBlueprint->shouldReceive('table')->andReturn('my_table');

        $this->assertEquals($expected, $config->get($baseBlueprint, $key));
    }

    public function provideDataForTestGet()
    {
        return [
            'Basic Key' => [
                [
                    '*' => [
                        'Key' => 'Value'
                    ],
                ],
                'Key',
                'Value'
            ],
            'Schema Key' => [
                [
                    'test' => [
                        'schemaKey' => 'Schema Value'
                    ],
                ],
                'schemaKey',
                'Schema Value'
            ],
            'Qualified Table Key' => [
                [
                    'test' => [
                        'qfKey' => 'Qualified Table Value'
                    ],
                ],
                'qfKey',
                'Qualified Table Value'
            ],
            'Connection Basic Key' => [
                [
                    '@connections' => [
                        'test_connection' => [
                            'cKey' => 'Connection Value'
                        ],
                    ]
                ],
                'cKey',
                'Connection Value'
            ],
            'Connection Schema Key' => [
                [
                    '@connections' => [
                        'test_connection' => [
                            'test' => [
                                'csKey' => 'Connection Schema Value'
                            ]
                        ],
                    ]
                ],
                'csKey',
                'Connection Schema Value'
            ],
            'Connection Table Key' => [
                [
                    '@connections' => [
                        'test_connection' => [
                            'my_table' => [
                                'ctKey' => 'Connection Table Value'
                            ]
                        ],
                    ]
                ],
                'ctKey',
                'Connection Table Value'
            ],
            'Test Hierarchy Override for Schema' => [
                [
                    '*' => [
                        'FirstKey' => 'Some Value'
                    ],
                    'test' => [
                        'FirstKey' => 'A Second Value'
                    ]
                ],
                'FirstKey',
                'A Second Value'
            ],
            'Test Hierarchy Override for Qualified Table' => [
                [
                    '*' => [
                        'FirstKey' => 'Some Value'
                    ],
                    'test' => [
                        'FirstKey' => 'A Second Value',
                        'my_table' => [
                            'FirstKey' => 'A Third Value'
                        ]
                    ],
                ],
                'FirstKey',
                'A Third Value'
            ],
            'Test Hierarchy Override for Connection Basic Key' => [
                [
                    '*' => [
                        'FirstKey' => 'Some Value'
                    ],
                    'test' => [
                        'FirstKey' => 'A Second Value',
                        'my_table' => [
                            'FirstKey' => 'A Third Value'
                        ]
                    ],
                    '@connections' => [
                        'test_connection' => [
                            'FirstKey' => 'A Fourth Value',
                        ]
                    ]
                ],
                'FirstKey',
                'A Fourth Value'
            ],
            'Test Hierarchy Override for Connection Schema Key' => [
                [
                    '*' => [
                        'FirstKey' => 'Some Value'
                    ],
                    'test' => [
                        'FirstKey' => 'A Second Value',
                        'my_table' => [
                            'FirstKey' => 'A Third Value'
                        ]
                    ],
                    '@connections' => [
                        'test_connection' => [
                            'FirstKey' => 'A Fourth Value',
                            'test' => [
                                'FirstKey' => 'A Fifth Value'
                            ]
                        ]
                    ]
                ],
                'FirstKey',
                'A Fifth Value'
            ],
            'Test Hierarchy Override for Connection Table Key' => [
                [
                    '*' => [
                        'FirstKey' => 'Some Value'
                    ],
                    'test' => [
                        'FirstKey' => 'A Second Value',
                        'my_table' => [
                            'FirstKey' => 'A Third Value'
                        ]
                    ],
                    '@connections' => [
                        'test_connection' => [
                            'FirstKey' => 'A Fourth Value',
                            'test' => [
                                'FirstKey' => 'A Fifth Value',
                            ],
                            'my_table' => [
                                'FirstKey' => 'A Sixth Value',
                            ]
                        ]
                    ]
                ],
                'FirstKey',
                'A Sixth Value'
            ],
        ];
    }
}