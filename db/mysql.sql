set names utf8;

drop database if exists spider;
create database spider;
use spider;

drop table if exists `crawl_page`;
create table `crawl_page` (
    `id` int(11) not null auto_increment,
    `url` varchar(1024) not null default '' comment '页面访问url',
    `url_hash_code` varbinary(32) not null default '' comment '页面URL MD5值',
    `page_summary_context` varchar(1024) default '' comment '页面简要内容',
    `page_file_path` varchar(255) default '' comment '页面存储地址',
    `c_t` int(11) not null default 0 comment '创建时间',
    primary key(`id`),
    key `i_url_code` (`url_hash_code`)
)engine=innodb default charset=utf8 comment='爬行信息表';

drop table if exists `crawl_queue`;
create table `crawl_queue` (
    `id` int(11) not null auto_increment,
    `deep_level` tinyint(4) not null default 0 comment '当前深度',
    `url` varchar(1024) not null default '' comment '页面url',
    `is_crawl` tinyint(4) not null default 0 comment '是否爬过，0:未爬过；1:已爬过',
    `status` tinyint(4) not null default 1 comment '爬行结果，0:失败；1:成功',
    `c_t` int(11) not null default 0 comment '创建时间',
    `u_t` int(11) not null default 0 comment '更新时间',
    primary key (`id`)
)engine=innodb default charset=utf8 comment='爬行队列';
