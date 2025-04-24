CREATE TABLE `acl_users`
(
  `id`       varchar(25)  NOT NULL,
  `name`     varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

CREATE TABLE `acl_roles`
(
  `id`        varchar(6)   NOT NULL,
  `name`      varchar(255) NOT NULL,
  `parent_id` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY         `ForeignKey_acl_roles_parent_id` (`parent_id`),
  CONSTRAINT `ForeignKey_acl_roles_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `acl_roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

CREATE TABLE `acl_privilege`
(
  `id`   varchar(10)  NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

CREATE TABLE `acl_resource`
(
  `id`        varchar(6)   NOT NULL,
  `name`      varchar(255) NOT NULL,
  `pattern`   varchar(255) NOT NULL,
  `parent_id` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY         `ForeignKey_acl_resource_parent_id` (`parent_id`),
  CONSTRAINT `ForeignKey_acl_resource_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `acl_resource` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

CREATE TABLE `acl_user_roles`
(
  `id`       varchar(6)  NOT NULL,
  `user_id`  varchar(25) NOT NULL,
  `roles_id` varchar(6)  NOT NULL,
  PRIMARY KEY (`id`),
  KEY        `ForeignKey_acl_user_roles_user_id` (`user_id`),
  KEY        `ForeignKey_acl_user_roles_roles_id` (`roles_id`),
  CONSTRAINT `ForeignKey_acl_user_roles_roles_id` FOREIGN KEY (`roles_id`) REFERENCES `acl_roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `ForeignKey_acl_user_roles_user_id` FOREIGN KEY (`user_id`) REFERENCES `acl_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

CREATE TABLE `acl_rules`
(
  `id`           varchar(6) NOT NULL,
  `role_id`      varchar(6) NOT NULL,
  `resource_id`  varchar(6) NOT NULL,
  `privilege_id` varchar(6) NOT NULL,
  `allow_flag`   tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY            `ForeignKey_acl_rules_role_id` (`role_id`),
  KEY            `ForeignKey_acl_rules_resource_id` (`resource_id`),
  KEY            `ForeignKey_acl_rules_privilege_id` (`privilege_id`),
  CONSTRAINT `ForeignKey_acl_rules_privilege_id` FOREIGN KEY (`privilege_id`) REFERENCES `acl_privilege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `ForeignKey_acl_rules_resource_id` FOREIGN KEY (`resource_id`) REFERENCES `acl_resource` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `ForeignKey_acl_rules_role_id` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;
