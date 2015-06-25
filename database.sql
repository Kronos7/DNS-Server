
CREATE TABLE IF NOT EXISTS `Updated_Records` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `domain` varchar(250) NOT NULL,
  `ip` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=166 ;

CREATE TABLE IF NOT EXISTS `My_Records` (
  `id` int(11) NOT NULL,
  `domain` varchar(500) NOT NULL,
  `ip` varchar(500) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
