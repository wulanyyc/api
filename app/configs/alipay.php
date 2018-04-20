<?php

return [
    //应用ID,您的APPID。
    'app_id' => "2017122701256111",

    //商户私钥，您的原始格式RSA私钥
    'merchant_private_key' => "MIIEogIBAAKCAQEAsdqvzc6HB5F2tay4zvb46PqprEuIq8po2Jdx4uTgDmkCayz8Sbdu157R55AlUTKOITC4cLMMJNpj1HMb08ARUfvfi01bAtrPXHizDiWYjv/Lm25STq1DYs3B5n/Ux3RI2qcPp2BrUE7lL+HxI9x9s0tsr+eOWIEHb7X2ZtjNZ98/v42NMX6hgT/pbM+lUSb7Xc3PgjSE7hrWiA+3vrryUf2Qh2jh2vJwlFE0LkTToeTQshxk0wjnsprn0IGxCFtjCXB4C8sQU5g0ZGWuEAQXcz8uqBohjRBmdoJGeQ/vdjkCvWhSb/KL71y0qDEBYaIGreWXWhSHPsDFLhxrLAhpZwIDAQABAoIBAA7INUpuuuxXG7230FhrUH7UrpuCX6afmR0/YRjRmwm3kprjz0g1NcI4Egwj/+YCxKtfiETdrMm3WQrEUnDECux8ebTzRfgzxX5TDdbEKyzfd8XAYpjlb69bmJ0WzNGTCacasYNg0SqIlCXpqogngE8RmsP4A+25M6wGPKeJp3DViqhVnWfXAR4BWj1QR2XmF79+vJt7VjTb3hykt3072rBJ2rhH8GTRRQ+i0rmPBxIZCBvymeD+Wg9XvdkJ9QdDB/Qp1ArI0c2Tb05qXrSAx/b7ZdEv9t548ps6Bysu/zUmBAmTDa7ABbbs/iO/CNUt2c81Ped7TK+JsRi3OcHmmbECgYEA7Ef933p5sDjSH3CYIGrgMcNybPwPb6KuLHR+YGU/CO0bEyAHoR8SnN3OgqEjMHPA8WAbhSLwSe5mLwR+T8elInnUVCJeTO+usU8pNWgsm34BDSJc0XtZBcJJY0qAoWHftVG+NGWwPTZ76n/pUfo1x74k16jVKl0ERVVJWnEnFP8CgYEAwLJv94cn3ULcaV9P4LnXCsMU7fU0X07OC9shepnC+hJgif42sAmUEkWsRSe4ePhGdBIxemzst6E/ndSHcSzIJf9t4l4IxShapIF9Fp+AC5iXh/+RyiZ/ij4J3tkb59nrXVNEosXL2Y7IBLrEA/2xAUWd0uF1Tx5wwVfwmuYyI5kCgYAhczevsakUl5a3uLrwq/C9WswSPcT5qvA5fux9PRglbVvN41TxLHL4HjakK6fNrjV8dnyu5nlaHhP9SAeRx9PLA7BZkNwEzFHQ7EILO4pFIRuI/nphdbLuq8iz89IuBqsjAkWJKXQ/1jzIv/8KCgAfHP4eQdqburtmDWLrGOXNhQKBgATBIlQYKJqj+K485MqlqzGfyJdqcmVbm2fde0D8JDxpUap19EF5qwacY3i9Vybc8VgRzti3cUvClcA+Ky1WroWJfNuV+F9s9HeUnlJ9kvo2RJ7dZmp3crQlfbba4n5g+RavZEUj3ji69iJvvUBf9QsT/aXjXDKcjKmoks4vmdMZAoGAYCrA6BMf9rzMEp995puFGPYaE1aT/PbvB3mY7jzbuBtQuMqsBtFCuxu9bOGd6j7z0wUaxXLYC4wvk28kfeVrjmls9iEd+vkgyb6xHtyjxaek8eU1cG+5/DjfJa6c4UXBwOSe7GwhKy8GvVr8O5LslK3NuLR9jUBexw+xZ+MtPs4=",
    
    //异步通知地址
    'notify_url' => "http://guoguojia.vip/pay/aliwap",
    
    //同步跳转
    'return_url' => "http://guoguojia.vip/pay",

    //编码格式
    'charset' => "UTF-8",

    //签名方式
    'sign_type'=>"RSA2",

    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAp7197K4ggRF4SJVCWpeF9aD0LQ8GJW76Od9spTXfCeNqyQDO/JErYIAgBZ21y8ANDher2mcYRAPXmNeTt4hPRxnWLjMlaQ0e11kDFYpOOxFPSea4iDIMvdws4T/sB5AKEn6G1RcYQhyFBQAOjzgH1hbMPag2Kzt1AAMdOQx82emBHPTzK5TII9JbRDop0mh6LeeFz4nDU/qmWbU9BMs2Rhi8Chy3/uRALEpkfY3+NEFhOSTpoPuIAY1YVbv8I/zGn4Xuw+fhkDk3VzllhHL3RMGfD5dgKXQlp5SWz4qUW1Y/Q4tNMXgrbmYYymqP5dzmHEZRhrbK+5vj7PnxWY5gQQIDAQAB",
];