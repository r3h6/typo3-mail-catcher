#
# Table structure for table 'tx_mailcatcher_domain_model_message'
#
CREATE TABLE tx_mailcatcher_domain_model_message (
    message_id varchar(255) DEFAULT '' NOT NULL,
    subject varchar(255) DEFAULT '' NOT NULL,
    from varchar(255) DEFAULT '' NOT NULL,
    to text,
    serialized mediumblob,
    source mediumtext,
);