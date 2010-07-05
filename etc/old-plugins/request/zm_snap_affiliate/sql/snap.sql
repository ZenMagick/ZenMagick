
create table snap_referrers (
	referrer_customers_id int(11) not null primary key,
	referrer_key varchar(32) not null,
	referrer_homepage text not null,
	referrer_approved tinyint(4) not null,
	referrer_banned tinyint(4) not null,
	referrer_commission float not null
);

create table snap_commission (
	commission_orders_id int(11) not null primary key,
	commission_referrer_key varchar(96) not null,
	commission_rate float not null,
	commission_paid datetime not null
);
