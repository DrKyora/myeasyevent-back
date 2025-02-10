<?php

switch($_SERVER['HTTP_HOST']){
    case '':
        define(constant_name: 'PROJECT_NAME', value: 'myeasyevent-back');
        $frontEndAddress = '';
        break;
    default:
        define(constant_name: 'PROJECT_NAME',value: 'myeasyevent-back');
        $frontEndAddress = 'https://localhost/myeasyevent-front/';
        break;
}

