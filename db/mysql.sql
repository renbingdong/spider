set names utf8;

create database spider;
use spider;

create table `crawl_page` (
    `id` int(11) not null auto_increment,
    `url` varchar(255) not null default '' comment '页面访问url',
    `url_hash_code` varbinary(32) not null default '' comment '页面URL MD5值',
    `page_summary_context` varchar(500) default '' comment '页面简要内容',
    `page_file_path` varchar(255) default '' comment '页面存储地址',
    `c_t` int(11) not null default 0 comment '创建时间',
    primary key(`id`),
    key `i_url_code` (`url_hash_code`)
)engine=innodb default charset=utf8;

