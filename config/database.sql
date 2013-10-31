-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


--
-- Table `tl_iso_payment_modules`
--

CREATE TABLE `tl_iso_payment_modules` (
  `viveum_pspid` varchar(255) NOT NULL default '',
  `viveum_hash_in` varchar(32) NOT NULL default '',
  `viveum_hash_out` varchar(32) NOT NULL default '',
  `viveum_dynamic_template` varchar(255) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

