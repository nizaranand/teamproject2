create schema if not exists team_project_2;
use team_project_2;
create table if not exists user_info(
    user_id int unique primary key not null auto_increment
    , password varchar(60) not null
    , email varchar(50) unique not null
    , first_name varchar(50) not null
    , last_name varchar(50) not null
    , gender tinyint(1) not null
    , birthday date
    , user_session_ip varchar(15)
    , picture_extension varchar(4)
    );
create table if not exists status_update(
    status_id int key not null auto_increment
    , user_id int not null
    , message tinytext not null
    , time_posted timestamp not null
    )
    ;
create table if not exists friend(
    initiator_id int not null
    , recipient_id int not null
    , accepted tinyint(1) not null
    )
    ;
