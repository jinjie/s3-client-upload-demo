# S3 Browser Upload with PHP Demo

A demo to try uploading file to Amazon S3 from the browser without exposing the user API keys by using presigned url.

## What this demo showcase

- Uploading files
- Adding a prefix to upload so that it does not overwrite with other files with the same file name
    - Using session and timestamp prefix to the key

## Resources

Set up necessary permissions on AWS

https://softwareontheroad.com/aws-s3-secure-direct-upload/

Amazon S3 Presigned POSTs

https://docs.amazonaws.cn/zh_cn/aws-sdk-php/guide/latest/service/s3-presigned-post.html

AJAX FormData POST

https://stackoverflow.com/questions/21044798/how-to-use-formdata-for-ajax-file-upload

Other resources

https://stackoverflow.com/questions/56080480/aws-s3-upload-access-denied-through-presigned-post-generated-with-aws-sdk-php

## Author

Kong Jin Jie / [Swift DevLabs](https://www.swiftdev.sg/)
