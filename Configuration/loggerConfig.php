<?php
    $loggerConfiguration = array(
        'rootLogger' => array(
            'level' => 'INFO',
            'appenders' => array('myMainAppender')
        ),
        'loggers' => array(
            'main' => array(
                'appenders' => array('myMainAppender')
            ),
            'proxy' => array(
                'appenders' => array('myMainAppender')
            ),
            'UDRM' => array(
                'appenders' => array('myMainAppender')
            ),
            'FPS' => array(
                'appenders' => array('myMainAppender')
            ),
            'DTO' => array(
                'appenders' => array('myMainAppender')
            )
        ),
        'appenders' => array(
            'myMainAppender' => array(
                'class' => 'LoggerAppenderDailyFile',
                'layout' => array(
                    'class' => 'LoggerLayoutPattern',
                    'params' => array(
                        'conversionPattern' => "%date [%level] [%logger] %s{suid} %message%newline"
                    )
                ),
                'params' => array(
                    'file' => 'logs/embedServicesHourlyLog-%s.log',
                    'datePattern' => 'Y-m-d.H'
                )
            )
        )
    );
?>