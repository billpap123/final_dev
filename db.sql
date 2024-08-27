drop database if exists finaldb;
create database finaldb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci; 
use finaldb;


create table if not exists `user`(
`user_id` int(11) not null AUTO_INCREMENT,
`username` varchar(30) not null,
`password` varchar(255) not null,
`fullname` varchar(50) not null,
`phone_number` varchar(20) not null,
`user_type` ENUM('Admin', 'Volunteer', 'Civilian') NOT NULL,
`location` POINT NOT NULL,
PRIMARY KEY (`user_id`),
UNIQUE (`username`)
);


create table if not exists `items`(
`item_id` int(11) NOT NULL,
`item_name` varchar(200) NOT NULL,
`category_id` int(11) DEFAULT NULL,
PRIMARY KEY (`item_id`)
);


create table if not exists `item_categories`(
`cat_id` int(11) NOT NULL,
`cat_name` varchar(200) DEFAULT NULL,
PRIMARY KEY (`cat_id`),
UNIQUE (`cat_name`)
);


create table if not exists `item_details`(
`it_id` int(11) NOT NULL,
`detail_name` varchar(50) DEFAULT NULL,
`detail_value` varchar(255) DEFAULT NULL,
PRIMARY KEY (`it_id`), 
FOREIGN KEY (`it_id`) REFERENCES `items`(`item_id`)
ON DELETE CASCADE ON UPDATE CASCADE 
);



create table if not exists `vehicle`(
`vehicle_id` int(5) not null,
`volunteer_id` int(11) not null, 
`vehicle_name` varchar(50) not null,
`current_location` POINT,
`current_load` int(3),
`current_task` int(11) DEFAULT NULL,
`status` ENUM('Available', 'Busy'),
PRIMARY KEY (`vehicle_id`),
UNIQUE (`volunteer_id`),
FOREIGN KEY (`volunteer_id`) REFERENCES `user`(`user_id`)
);


create table if not exists `requests`(
`request_id` int(11) not null AUTO_INCREMENT,
`civilian_id` int(11) not null,
`item_id` int(11) NOT NULL,
`quantity_requested` int(5) DEFAULT NULL,
`date_created` DATE NOT NULL,
`status` ENUM('Pending', 'Processing', 'Completed', 'Cancelled'),
`num_of_people` int(4), 
PRIMARY KEY (`request_id`),
FOREIGN KEY (`civilian_id`) REFERENCES `user`(`user_id`)
ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
);



create table if not exists `offers`(
`offer_id` int(11) not null AUTO_INCREMENT,
`civilian_id` int(11) not null,
`item_id` int(11) NOT NULL,
`quantity_offered` int(4) DEFAULT null,
`date_created` DATE not null,
`status` ENUM('Pending', 'Processing', 'Completed', 'Cancelled'),
PRIMARY KEY (`offer_id`),
FOREIGN KEY (`civilian_id`) REFERENCES `user`(`user_id`)
ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
);





create table if not exists `storage`(
`storage_id` int(1) DEFAULT " 1",
`storage_location` POINT, 
PRIMARY KEY (`storage_id`)
);


  
create table if not exists `tasks`( 
`task_id` int(11) not null AUTO_INCREMENT,
`type` ENUM('Pickup', 'Delivery'),
`vehicle_id` int(5) DEFAULT null,
`request_id` int(11) DEFAULT null,
`offer_id` int(11) DEFAULT null,
`status` ENUM('Processing', 'Completed', 'Cancelled'),
`assigned_date` DATE not null,
`completed_date` DATE DEFAULT null,
PRIMARY KEY (`task_id`),
FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle`(`vehicle_id`),
FOREIGN KEY(`request_id`) REFERENCES `requests`(`request_id`)
ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (`offer_id`) REFERENCES `offers`(`offer_id`)
ON DELETE CASCADE ON UPDATE CASCADE,
CHECK (request_id IS NOT NULL OR offer_id IS NOT NULL)
);

  

create table if not exists `announcement`(
`announcement_id` int(11) not null AUTO_INCREMENT,
`admin_id` int(11) not null,
`item_id` int(11) not null,
`quantity_needed` int(4),
`date_created` DATE,
PRIMARY KEY (`announcement_id`),
FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`),
FOREIGN KEY (`admin_id`) REFERENCES `user`(`user_id`)
);



ALTER TABLE `items` ADD FOREIGN KEY (`category_id`) REFERENCES `item_categories`(`cat_id`)
on DELETE cascade on UPDATE cascade;

ALTER TABLE `vehicle` ADD FOREIGN KEY (`current_task`) REFERENCES `tasks`(`task_id`) 
on DELETE cascade on UPDATE cascade;

ALTER TABLE `items` ADD `quantity` int(10);