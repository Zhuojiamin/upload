DROP TABLE IF EXISTS pw_advt;
CREATE TABLE pw_advt (
  id int(8) unsigned NOT NULL auto_increment,
  `subject` varchar(100) NOT NULL,
  hits int(10) NOT NULL,
  width int(4) NOT NULL,
  height int(4) NOT NULL,
  postdate int(10) NOT NULL,
  `type` tinyint(1) NOT NULL,
  advdata text NOT NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_albums;
CREATE TABLE pw_albums (
  aid int(10) NOT NULL auto_increment,
  uid mediumint(8) NOT NULL,
  author varchar(20) NOT NULL,
  `password` varchar(40) NOT NULL,
  cid smallint(6) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `postdate` int(10) NOT NULL,
  lastpost int(10) NOT NULL,
  lastreplies int(10) NOT NULL,
  photos int(10) NOT NULL,
  hits int(10) NOT NULL,
  replies int(10) NOT NULL,
  topped tinyint(1) NOT NULL,
  digest tinyint(1) NOT NULL,
  locked tinyint(1) NOT NULL,
  allowreply tinyint(1) NOT NULL,
  ifcheck tinyint(1) NOT NULL,
  ifwordsfb tinyint(1) NOT NULL,
  ifhide tinyint(1) NOT NULL,
  ifconvert tinyint(1) NOT NULL,
  footprints int(10) NOT NULL,
  hpagepid mediumint(8) NOT NULL default '-1',
  `descrip` text NOT NULL,
  uploads mediumtext NOT NULL,
  pushlog varchar(255) NOT NULL,
  PRIMARY KEY  (aid),
  KEY uid (uid,ifcheck,postdate),
  KEY cid (cid,ifcheck,ifhide),
  KEY ifcheck (ifcheck,ifhide),
  KEY postdate (postdate,ifcheck)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_bbsclass;
CREATE TABLE pw_bbsclass (
  fid int(10) unsigned NOT NULL,
  name varchar(100) NOT NULL,
  vieworder tinyint(3) NOT NULL,
  _ifshow tinyint(1) NOT NULL default '0',
  cateshow tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (fid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_blog;
CREATE TABLE pw_blog (
  itemid int(10) unsigned NOT NULL default '0',
  tags mediumtext NOT NULL,
  userip varchar(15) NOT NULL default '',
  ifsign tinyint(1) NOT NULL default '0',
  ipfrom varchar(80) NOT NULL default '',
  pushlog varchar(255) NOT NULL default '',
  ifconvert tinyint(1) NOT NULL default '0',
  content mediumtext NOT NULL,
  PRIMARY KEY  (itemid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_blogfriend;
CREATE TABLE pw_blogfriend (
  id int(10) unsigned NOT NULL auto_increment,
  gid mediumint(8) unsigned NOT NULL,
  uid mediumint(8) unsigned NOT NULL,
  fuid mediumint(8) unsigned NOT NULL,
  fdate int(10) NOT NULL,
  ifcheck tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY uid (uid,fuid),
  KEY ifcheck (ifcheck)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_blogfriendg;
CREATE TABLE pw_blogfriendg (
  gid mediumint(8) NOT NULL auto_increment,
  uid mediumint(8) NOT NULL,
  gname varchar(100) NOT NULL,
  gnum smallint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (gid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_bloginfo;
CREATE TABLE pw_bloginfo (
  id smallint(3) unsigned NOT NULL auto_increment,
  newmember varchar(15) NOT NULL default '',
  totalmember mediumint(8) unsigned NOT NULL default '0',
  totalblogs mediumint(8) unsigned NOT NULL default '0',
  totalalbums mediumint(8) unsigned NOT NULL default '0',
  totalmalbums mediumint(8) unsigned NOT NULL default '0',
  tdblogs smallint(6) unsigned NOT NULL default '0',
  tdtcontrol int(10) unsigned NOT NULL default '0',
  KEY id (id)
) ENGINE=MyISAM;
INSERT INTO pw_bloginfo VALUES (1,'',0,0,0,0,0,0);

DROP TABLE IF EXISTS pw_blogvote;
CREATE TABLE pw_blogvote (
  id mediumint(8) unsigned NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL,
  voteitem mediumtext NOT NULL,
  content mediumtext NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  votedate int(10) unsigned NOT NULL,
  _ifshow tinyint(1) unsigned NOT NULL,
  maxnum tinyint(3) NOT NULL,
  _ifview tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY _ifshow (_ifshow)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_bookmark;
CREATE TABLE pw_bookmark (
  itemid int(10) unsigned NOT NULL,
  tags mediumtext NOT NULL,
  userip varchar(15) NOT NULL,
  ifsign tinyint(1) NOT NULL default '0',
  ipfrom varchar(80) NOT NULL,
  pushlog varchar(255) NOT NULL,
  bookmarkurl varchar(255) NOT NULL,
  ifconvert tinyint(1) NOT NULL,
  content mediumtext NOT NULL,
  KEY itemid (itemid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_carticle;
CREATE TABLE pw_carticle (
  id int(10) NOT NULL auto_increment,
  itemid int(10) unsigned NOT NULL,
  uid int(10) NOT NULL,
  touid int(10) NOT NULL,
  cdate int(10) NOT NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_categories;
CREATE TABLE pw_categories (
  cid mediumint(8) unsigned NOT NULL auto_increment,
  cup mediumint(8) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL,
  descrip varchar(255) NOT NULL,
  cupinfo varchar(255) NOT NULL,
  counts int(10) unsigned NOT NULL,
  vieworder tinyint(3) NOT NULL,
  _ifshow tinyint(1) NOT NULL,
  catetype varchar(50) NOT NULL,
  fid int(8) NOT NULL default '0',
  PRIMARY KEY  (cid),
  KEY catetype (catetype),
  KEY cup (cup,catetype)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_collections;
CREATE TABLE pw_collections (
  id int(10) unsigned NOT NULL auto_increment,
  itemid int(10) unsigned NOT NULL,
  uid int(10) NOT NULL,
  `type` varchar(10) NOT NULL,
  adddate int(10) NOT NULL,
  PRIMARY KEY  (id),
  KEY itemid (itemid),
  KEY uid (uid,`type`,itemid,adddate)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_comment;
CREATE TABLE pw_comment (
  id int(10) unsigned NOT NULL auto_increment,
  cid smallint(6) unsigned NOT NULL default '0',
  itemid int(10) unsigned NOT NULL default '0',
  `type` varchar(10) NOT NULL,
  uid mediumint(8) unsigned NOT NULL default '0',
  author varchar(20) NOT NULL,
  authorid mediumint(8) unsigned NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  `subject` varchar(130) NOT NULL default '',
  userip varchar(15) NOT NULL default '',
  ipfrom varchar(30) NOT NULL default '',
  ifcheck tinyint(1) NOT NULL default '0',
  ifwordsfb tinyint(1) NOT NULL default '0',
  ifconvert tinyint(1) NOT NULL default '0',
  content mediumtext NOT NULL,
  replydate int(10) unsigned NOT NULL default '0',
  reply mediumtext NOT NULL,
  PRIMARY KEY  (id),
  KEY itemid (itemid),
  KEY authorid (authorid),
  KEY author (author),
  KEY userip (userip,postdate),
  KEY ifcheck (ifcheck,itemid,uid,postdate)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_footprint;
CREATE TABLE pw_footprint (
  id int(10) NOT NULL auto_increment,
  itemid int(10) unsigned NOT NULL,
  type varchar(10) NOT NULL default '',
  uid mediumint(8) NOT NULL,
  fdate int(10) NOT NULL,
  PRIMARY KEY  (id),
  KEY uid (uid),
  KEY itemid (itemid,uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_gbook;
CREATE TABLE pw_gbook (
  id int(11) NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  author varchar(20) NOT NULL default '',
  authorid mediumint(8) NOT NULL default '0',
  authoricon varchar(100) NOT NULL,
  `type` tinyint(1) NOT NULL default '0',
  postdate int(10) NOT NULL default '0',
  userip varchar(15) NOT NULL,
  content mediumtext NOT NULL,
  replydate int(10) NOT NULL default '0',
  reply mediumtext NOT NULL,
  ifwordsfb tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY uid (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_group;
CREATE TABLE pw_group (
  gid tinyint(3) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default 'member',
  title varchar(30) NOT NULL default '',
  img varchar(15) NOT NULL default '',
  creditneed int(10) NOT NULL default '0',
  ifdefault tinyint(1) NOT NULL default '0',
  mright text NOT NULL,
  admincp tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (gid),
  KEY admincp (admincp),
  KEY ifdefault (ifdefault)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_hobby;
CREATE TABLE pw_hobby (
  id mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  vieworder tinyint(3) NOT NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_hobbyitem;
CREATE TABLE pw_hobbyitem (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hid mediumint(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  vieworder tinyint(3) NOT NULL,
  ifcheck tinyint(1) NOT NULL,
  PRIMARY KEY  (id),
  KEY hid (hid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_itemnav;
CREATE TABLE pw_itemnav (
  id int(8) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  url varchar(255) NOT NULL,
  vieworder int(4) NOT NULL,
  _ifblank tinyint(1) NOT NULL,
  _ifshow tinyint(1) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY  (id),
  KEY ifshow (_ifshow)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_items;
CREATE TABLE pw_items (
  itemid int(10) unsigned NOT NULL auto_increment,
  cid smallint(6) unsigned NOT NULL,
  bbsfid mediumint(8) unsigned NOT NULL,
  dirid mediumint(8) unsigned NOT NULL,
  uid mediumint(8) unsigned NOT NULL,
  transfer varchar(30) NOT NULL,
  author varchar(20) NOT NULL,
  `type` varchar(10) NOT NULL,
  icon varchar(3) NOT NULL,
  `subject` varchar(130) NOT NULL,
  postdate int(10) NOT NULL,
  lastpost int(10) NOT NULL,
  lastreplies int(10) NOT NULL default '0',
  hits int(10) NOT NULL,
  replies int(10) NOT NULL,
  topped tinyint(1) NOT NULL,
  digest tinyint(1) NOT NULL,
  locked tinyint(1) NOT NULL,
  allowreply tinyint(1) NOT NULL,
  folder tinyint(1) NOT NULL,
  ifcheck tinyint(1) NOT NULL default '1',
  ifwordsfb tinyint(1) NOT NULL default '0',
  ifhide tinyint(1) NOT NULL,
  footprints int(10) NOT NULL,
  uploads mediumtext NOT NULL,
  cmttext mediumtext NOT NULL,
  PRIMARY KEY  (itemid),
  KEY uid (uid,`type`,ifcheck,dirid,postdate),
  KEY cid (cid,ifcheck,ifhide),
  KEY ifcheck (ifcheck,ifhide,`type`),
  KEY postdate (postdate,ifcheck)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_itemtype;
CREATE TABLE pw_itemtype (
  typeid int(10) NOT NULL auto_increment,
  uid mediumint(8) NOT NULL,
  `type` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  vieworder tinyint(3) NOT NULL,
  PRIMARY KEY  (typeid),
  KEY uid (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_lcustom;
CREATE TABLE pw_lcustom (
  id mediumint(8) unsigned NOT NULL auto_increment,
  authorid mediumint(8) NOT NULL,
  sign varchar(30) NOT NULL,
  setdate int(10) unsigned NOT NULL,
  `subject` varchar(130) NOT NULL default '',
  content mediumtext NOT NULL,
  PRIMARY KEY  (id),
  KEY authorid (authorid,sign)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_malbums;
CREATE TABLE pw_malbums (
  maid int(10) NOT NULL auto_increment,
  uid mediumint(8) NOT NULL,
  `author` varchar(20) NOT NULL,
  cid smallint(6) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `postdate` int(10) NOT NULL,
  lastpost int(10) NOT NULL,
  lastreplies int(10) NOT NULL,
  musics int(10) NOT NULL,
  hits int(10) NOT NULL,
  replies int(10) NOT NULL,
  topped tinyint(1) NOT NULL,
  digest tinyint(1) NOT NULL,
  locked tinyint(1) NOT NULL,
  allowreply tinyint(1) NOT NULL,
  ifcheck tinyint(1) NOT NULL,
  ifwordsfb tinyint(1) NOT NULL,
  ifconvert tinyint(1) NOT NULL,
  footprints int(10) NOT NULL,
  hpageurl char(100) NOT NULL,
  `descrip` text NOT NULL,
  cmttext mediumtext NOT NULL,
  pushlog mediumtext NOT NULL,
  PRIMARY KEY  (maid),
  KEY uid (uid,ifcheck,postdate),
  KEY cid (cid,ifcheck),
  KEY ifcheck (ifcheck),
  KEY postdate (postdate,ifcheck)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_module;
CREATE TABLE pw_module (
  id mediumint(8) unsigned NOT NULL auto_increment,
  funcname varchar(13) NOT NULL,
  uid mediumint(8) NOT NULL default '0',
  `name` varchar(20) NOT NULL,
  everycache int(10) NOT NULL default '0',
  lastcache int(10) NOT NULL default '0',
  limitnum tinyint(4) NOT NULL default '0',
  shownum varchar(10) NOT NULL,
  ifshow tinyint(1) NOT NULL,
  cachetext tinytext NOT NULL,
  PRIMARY KEY  (id),
  KEY uid (uid,lastcache,everycache,ifshow),
  KEY funcname (funcname)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_msgs;
CREATE TABLE pw_msgs (
  mid int(10) unsigned NOT NULL auto_increment,
  touid mediumint(8) unsigned NOT NULL default '0',
  togroups varchar(80) NOT NULL default '',
  fromuid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  `type` enum('rebox','sebox','public') NOT NULL default 'rebox',
  bbsmid int(10) NOT NULL,
  ifnew tinyint(1) NOT NULL default '0',
  title varchar(130) NOT NULL default '',
  mdate int(10) unsigned NOT NULL default '0',
  content text NOT NULL,
  PRIMARY KEY  (mid),
  KEY touid (touid),
  KEY fromuid (fromuid,mdate),
  KEY `type` (`type`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_music;
CREATE TABLE pw_music (
  mid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) NOT NULL,
  maid int(10) NOT NULL,
  `name` char(80) NOT NULL default '0',
  murl char(200) NOT NULL,
  `posttime` int(10) NOT NULL,
  singer varchar(50) NOT NULL,
  tags char(20) NOT NULL,
  mhits mediumint(10) NOT NULL,
  mreplies int(10) NOT NULL,
  mlastreplies int(10) NOT NULL,
  `descrip` varchar(200) NOT NULL,
  ifwordsfb tinyint(1) NOT NULL,
  PRIMARY KEY  (mid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_notice;
CREATE TABLE pw_notice (
  aid smallint(6) unsigned NOT NULL auto_increment,
  vieworder smallint(6) NOT NULL default '0',
  author varchar(15) NOT NULL default '',
  startdate varchar(15) NOT NULL default '',
  url varchar(80) NOT NULL,
  `subject` varchar(130) NOT NULL default '',
  content mediumtext NOT NULL,
  PRIMARY KEY  (aid),
  KEY vieworder (vieworder,startdate)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_photo;
CREATE TABLE pw_photo (
  pid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL,
  cid smallint(6) unsigned NOT NULL,
  aid int(10) unsigned NOT NULL,
  `name` char(80) NOT NULL,
  attachurl char(80) NOT NULL,
  uploadtime int(10) unsigned NOT NULL,
  tags mediumtext NOT NULL,
  phits int(10) NOT NULL,
  preplies int(10) NOT NULL,
  pfootprints int(10) NOT NULL,
  plastreplies int(10) NOT NULL,
  pushlog varchar(255) NOT NULL,
  `descrip` mediumtext NOT NULL,
  cmttext mediumtext NOT NULL,
  pallowreply tinyint(1) NOT NULL,
  ifhpage tinyint(1) NOT NULL,
  ifwordsfb tinyint(1) NOT NULL,
  ifthumb tinyint(1) unsigned NOT NULL,
  PRIMARY KEY  (pid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_replace;
CREATE TABLE pw_replace (
  id smallint(6) unsigned NOT NULL auto_increment,
  word varchar(100) NOT NULL default '',
  wordreplace varchar(100) NOT NULL default '',
  `type` enum('replace','forbid') NOT NULL default 'replace',
  PRIMARY KEY  (id),
  KEY `type` (`type`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_rightset;
CREATE TABLE pw_rightset (
  gid tinyint(3) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (gid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_schindex;
CREATE TABLE pw_schindex (
  sid mediumint(8) unsigned NOT NULL auto_increment,
  sorderby varchar(10) NOT NULL,
  schline varchar(32) NOT NULL,
  schtime int(10) unsigned NOT NULL default '0',
  total mediumint(8) unsigned NOT NULL default '0',
  skeyword varchar(255) default NULL,
  schedid text NOT NULL,
  PRIMARY KEY  (sid),
  KEY schline (schline),
  KEY schtime (schtime)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_setforms;
CREATE TABLE pw_setforms (
  id int(10) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  ifopen tinyint(1) NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_setting;
CREATE TABLE pw_setting (
  db_name varchar(30) NOT NULL default '',
  db_value text NOT NULL,
  decrip text,
  PRIMARY KEY  (db_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_share;
CREATE TABLE pw_share (
  sid smallint(6) unsigned NOT NULL auto_increment,
  threadorder tinyint(3) NOT NULL default '0',
  `name` char(100) NOT NULL default '',
  url char(100) NOT NULL default '',
  descrip char(200) NOT NULL default '0',
  logo char(100) NOT NULL default '',
  ifcheck tinyint(1) NOT NULL default '0',
  linkuid mediumint(8) NOT NULL default '0',
  linktime int(10) NOT NULL default '0',
  PRIMARY KEY  (sid),
  KEY ifcheck (ifcheck)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_smile;
CREATE TABLE pw_smile (
  id smallint(6) unsigned NOT NULL auto_increment,
  path varchar(20) NOT NULL default '',
  `name` varchar(20) NOT NULL default '',
  descipt varchar(100) NOT NULL default '',
  vieworder tinyint(2) NOT NULL default '0',
  `type` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_style;
CREATE TABLE pw_style (
  sid smallint(6) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL default '',
  stylepath char(50) NOT NULL default '',
  tplpath char(50) NOT NULL default '',
  i_table tinyint(1) unsigned NOT NULL default '0',
  tablecolor char(7) NOT NULL default '',
  tablewidth char(4) NOT NULL default '',
  mtablewidth char(4) NOT NULL default '',
  forumcolorone char(7) NOT NULL default '',
  forumcolortwo char(7) NOT NULL default '',
  threadcolorone char(7) NOT NULL default '',
  threadcolortwo char(7) NOT NULL default '',
  readcolorone char(7) NOT NULL default '',
  readcolortwo char(7) NOT NULL default '',
  maincolor char(7) NOT NULL default '',
  PRIMARY KEY  (sid),
  KEY `name` (`name`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_taginfo;
CREATE TABLE pw_taginfo (
  tagid mediumint(8) unsigned NOT NULL default '0',
  tagname varchar(50) NOT NULL,
  uid mediumint(8) unsigned NOT NULL default '0',
  author varchar(20) NOT NULL,
  itemid int(10) unsigned NOT NULL default '0',
  tagtype varchar(50) NOT NULL,
  `subject` varchar(130) NOT NULL,
  addtime int(10) NOT NULL default '0',
  KEY itemid (itemid),
  KEY tagname (tagname,tagtype)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_btags;
CREATE TABLE pw_btags (
  tagid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL,
  tagname varchar(50) NOT NULL default '',
  blognum int(10) NOT NULL default '0',
  photonum int(10) NOT NULL default '0',
  bookmarknum int(10) NOT NULL,
  musicnum int(10) NOT NULL,
  allnum int(10) NOT NULL,
  iflock tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (tagid),
  KEY tagname (tagname)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_tblog;
CREATE TABLE pw_tblog (
  itemid int(10) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  teamid smallint(5) unsigned NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  `subject` char(130) NOT NULL default '',
  `type` enum('blog','photo','music','bookmark') NOT NULL default 'blog',
  `commend` tinyint(1) NOT NULL,
  KEY teamid (teamid,postdate)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_team;
CREATE TABLE pw_team (
  teamid mediumint(8) unsigned NOT NULL auto_increment,
  cid smallint(5) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  `name` char(30) NOT NULL default '',
  descrip char(255) NOT NULL default '',
  icon varchar(100) NOT NULL,
  notice mediumtext NOT NULL,
  blogtype char(255) NOT NULL default '',
  `level` tinyint(3) unsigned NOT NULL default '0',
  items int(10) unsigned NOT NULL default '0',
  blogs int(10) unsigned NOT NULL default '0',
  albums int(10) unsigned NOT NULL default '0',
  malbums int(10) unsigned NOT NULL default '0',
  bloggers int(10) unsigned NOT NULL default '0',
  `type` tinyint(1) NOT NULL default '0',
  lastid mediumint(8) unsigned NOT NULL default '0',
  commend tinyint(1) NOT NULL default '0',
  ifshow tinyint(1) NOT NULL default '0',
  gbooktype tinyint(1) NOT NULL default '0',
  visitdata text NOT NULL,
  PRIMARY KEY  (teamid),
  KEY cid (cid),
  KEY uid (uid),
  KEY ifshow (ifshow,cid,bloggers)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_tgbook;
CREATE TABLE pw_tgbook (
  id int(10) unsigned NOT NULL auto_increment,
  teamid mediumint(8) NOT NULL,
  uid mediumint(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  content text NOT NULL,
  postdate int(10) NOT NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_tuser;
CREATE TABLE pw_tuser (
  admin mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  teamid mediumint(8) unsigned NOT NULL default '0',
  joindate int(10) unsigned NOT NULL default '0',
  blogs int(10) unsigned NOT NULL default '0',
  ifcheck tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,teamid),
  KEY teamid (teamid),
  KEY uid (uid,teamid,ifcheck,admin)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_upload;
CREATE TABLE pw_upload (
  aid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  cid smallint(6) NOT NULL default '0',
  itemid int(10) unsigned NOT NULL default '0',
  `name` char(80) NOT NULL default '0',
  `type` char(15) NOT NULL,
  size int(10) unsigned NOT NULL default '0',
  hits int(10) NOT NULL,
  attachurl char(80) NOT NULL default '0',
  uploadtime int(10) unsigned NOT NULL default '0',
  descrip char(100) NOT NULL default '',
  atype varchar(10) NOT NULL,
  state tinyint(1) NOT NULL,
  ifthumb tinyint(1) unsigned NOT NULL,
  PRIMARY KEY  (aid),
  KEY itemid (itemid),
  KEY uid (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_user;
CREATE TABLE pw_user (
  uid mediumint(8) unsigned NOT NULL auto_increment,
  username varchar(20) NOT NULL default '',
  `password` varchar(40) NOT NULL default '',
  blogtitle varchar(30) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  publicmail tinyint(1) NOT NULL default '0',
  groupid tinyint(3) NOT NULL default '-1',
  memberid tinyint(3) NOT NULL default '0',
  icon varchar(100) NOT NULL default '',
  iconsize varchar(20) NOT NULL default '',
  gender tinyint(1) NOT NULL default '0',
  regdate int(10) unsigned NOT NULL default '0',
  qq varchar(12) NOT NULL default '',
  msn varchar(35) NOT NULL default '',
  yahoo varchar(35) NOT NULL default '',
  site varchar(75) NOT NULL default '',
  province varchar(10) NOT NULL default '',
  city varchar(20) NOT NULL default '',
  honor varchar(30) NOT NULL default '',
  blogs int(10) unsigned NOT NULL default '0',
  comments int(11) NOT NULL default '0',
  msgs int(11) NOT NULL default '0',
  views int(11) NOT NULL default '0',
  rvrc int(10) NOT NULL default '0',
  money int(10) NOT NULL default '0',
  credit int(10) NOT NULL default '0',
  commend tinyint(1) unsigned NOT NULL default '0',
  lastvisit int(10) unsigned NOT NULL default '0',
  thisvisit int(10) unsigned NOT NULL default '0',
  bday date NOT NULL default '0000-00-00',
  verify int(10) NOT NULL default '1',
  todaypost smallint(6) unsigned NOT NULL default '0',
  lastpost int(10) NOT NULL default '0',
  lastsearch int(10) NOT NULL,
  timedf smallint(6) NOT NULL default '0',
  datefm varchar(15) NOT NULL default '',
  onlineip varchar(15) NOT NULL,
  logincheck varchar(13) NOT NULL,
  onlinetime int(10) unsigned NOT NULL default '0',
  editor tinyint(1) NOT NULL default '0',
  signchange tinyint(1) NOT NULL default '0',
  dir varchar(255) NOT NULL default '',
  albums int(10) unsigned NOT NULL,
  photos int(10) unsigned NOT NULL,
  bookmarks int(10) unsigned NOT NULL,
  items int(10) unsigned NOT NULL,
  malbums int(10) unsigned NOT NULL,
  musics int(10) unsigned NOT NULL,
  friendview tinyint(1) NOT NULL default '0',
  friends mediumtext NOT NULL,
  PRIMARY KEY  (uid),
  KEY username (username),
  KEY email (email),
  KEY commend (commend),
  KEY province (province,city,gender),
  KEY regdate (regdate),
  KEY items (items)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_userhobby;
CREATE TABLE pw_userhobby (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) NOT NULL,
  hobbyid mediumint(8) NOT NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_userinfo;
CREATE TABLE pw_userinfo (
  uid mediumint(8) unsigned NOT NULL default '0',
  cid mediumint(8) NOT NULL default '0',
  style varchar(50) NOT NULL default '1',
  bbsid varchar(20) NOT NULL default '',
  bbsuid mediumint(8) NOT NULL default '0',
  maxbbsmid varchar(20) NOT NULL default '0,0',
  domainname varchar(20) NOT NULL default '',
  wshownum smallint(6) NOT NULL default '200',
  pshownum smallint(6) NOT NULL default '0',
  cshownum smallint(6) NOT NULL default '0',
  flashurl varchar(255) NOT NULL default '',
  bmusicurl varchar(255) NOT NULL default '',
  headerdb text NOT NULL,
  leftdb text NOT NULL,
  dirdb text NOT NULL,
  albumdb text NOT NULL,
  malbumdb text NOT NULL,
  commentdb text NOT NULL,
  teamdb text NOT NULL,
  frienddb text NOT NULL,
  lastvisitdb text NOT NULL,
  hobbydb text NOT NULL,
  signature text NOT NULL,
  introduce text NOT NULL,
  link text NOT NULL,
  klink text NOT NULL,
  notice text NOT NULL,
  adsips text NOT NULL,
  bbstids text NOT NULL,
  newpm tinyint(1) NOT NULL,
  gdcheck varchar(3) NOT NULL,
  qcheck varchar(3) NOT NULL,
  postnum varchar(15) NOT NULL,
  plimitnum varchar(15) NOT NULL,
  ifgbook tinyint(1) NOT NULL default '0',
  readmsg mediumtext NOT NULL,
  delmsg mediumtext NOT NULL,
  PRIMARY KEY  (uid),
  KEY cid (cid),
  KEY bbsuid (bbsuid),
  KEY domainname (domainname)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_userskin;
CREATE TABLE pw_userskin (
  id int(10) NOT NULL auto_increment,
  uid mediumint(8) NOT NULL,
  sign varchar(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  demo varchar(100) NOT NULL,
  css text NOT NULL,
  diycss text NOT NULL,
  PRIMARY KEY  (id),
  KEY sign (sign,uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_voteitem;
CREATE TABLE pw_voteitem (
  id mediumint(8) unsigned NOT NULL auto_increment,
  vid mediumint(8) unsigned NOT NULL,
  item varchar(255) NOT NULL,
  num mediumint(8) unsigned NOT NULL,
  voteduid text NOT NULL,
  PRIMARY KEY  (id),
  KEY vid (vid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_bgsqlcv;
CREATE TABLE pw_bgsqlcv (
  id int(10) NOT NULL auto_increment,
  var varchar(30) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

INSERT INTO pw_categories VALUES ('1','0','0','技术讨论','','','0','0','1','blog','0');
INSERT INTO pw_categories VALUES ('2','0','0','生活人生','','','0','0','1','blog','0');
INSERT INTO pw_categories VALUES ('3','0','0','经济大观','','','0','0','1','blog','0');
INSERT INTO pw_categories VALUES ('4','0','0','时尚动态','','','0','0','1','blog','0');
INSERT INTO pw_categories VALUES ('5','0','0','舞文弄墨','','','0','0','0','blog','0');
INSERT INTO pw_categories VALUES ('6','0','0','情感天地','','','0','0','0','blog','0');
INSERT INTO pw_categories VALUES ('7','0','0','原创文学','','','0','0','0','blog','0');
INSERT INTO pw_categories VALUES ('8','0','0','体育竞技','','','0','0','0','blog','0');
INSERT INTO pw_categories VALUES ('9','0','0','旅游杂记','','','0','0','0','blog','0');
INSERT INTO pw_categories VALUES ('10','0','0','随想杂谈','','','0','0','0','blog','0');
INSERT INTO pw_categories VALUES ('11','0','0','个人空间','','','0','0','1','user','0');
INSERT INTO pw_categories VALUES ('12','0','0','经济金融','','','0','0','1','user','0');
INSERT INTO pw_categories VALUES ('13','0','0','电脑网络','','','0','0','1','user','0');
INSERT INTO pw_categories VALUES ('14','0','0','教育学习','','','0','0','1','user','0');
INSERT INTO pw_categories VALUES ('15','0','0','情感绿洲','','','0','0','0','user','0');
INSERT INTO pw_categories VALUES ('16','0','0','娱乐休闲','','','0','0','0','user','0');
INSERT INTO pw_categories VALUES ('17','0','0','文学园地','','','0','0','0','user','0');
INSERT INTO pw_categories VALUES ('18','0','0','新闻出版','','','0','0','0','user','0');
INSERT INTO pw_categories VALUES ('19','0','0','体育竞技','','','0','0','0','user','0');
INSERT INTO pw_categories VALUES ('20','0','0','旅游自然','','','0','0','0','user','0');
INSERT INTO pw_categories VALUES ('21','0','0','明星写真','','','0','0','1','photo','0');
INSERT INTO pw_categories VALUES ('22','0','0','时装摄影','','','0','0','1','photo','0');
INSERT INTO pw_categories VALUES ('23','0','0','城市风光','','','0','0','1','photo','0');
INSERT INTO pw_categories VALUES ('24','0','0','日常生活','','','0','0','1','photo','0');
INSERT INTO pw_categories VALUES ('25','0','0','世界军事','','','0','0','0','photo','0');
INSERT INTO pw_categories VALUES ('26','0','0','体育运动','','','0','0','0','photo','0');
INSERT INTO pw_categories VALUES ('27','0','0','卡通漫画','','','0','0','0','photo','0');
INSERT INTO pw_categories VALUES ('28','0','0','生活幽默','','','0','0','0','photo','0');
INSERT INTO pw_categories VALUES ('29','0','0','游戏图库','','','0','0','0','photo','0');
INSERT INTO pw_categories VALUES ('30','0','0','校园情怀','','','0','0','0','photo','0');
INSERT INTO pw_categories VALUES ('31','0','0','流行金曲','','','0','0','1','music','0');
INSERT INTO pw_categories VALUES ('32','0','0','新歌速递','','','0','0','1','music','0');
INSERT INTO pw_categories VALUES ('33','0','0','影视金曲','','','0','0','1','music','0');
INSERT INTO pw_categories VALUES ('34','0','0','经典老歌','','','0','0','1','music','0');
INSERT INTO pw_categories VALUES ('35','0','0','民族歌曲','','','0','0','0','music','0');
INSERT INTO pw_categories VALUES ('36','0','0','校园歌曲','','','0','0','0','music','0');
INSERT INTO pw_categories VALUES ('37','0','0','军旅歌曲','','','0','0','0','music','0');
INSERT INTO pw_categories VALUES ('38','0','0','英文歌曲','','','0','0','0','music','0');
INSERT INTO pw_categories VALUES ('39','0','0','港台精选','','','0','0','0','music','0');
INSERT INTO pw_categories VALUES ('40','0','0','大陆精选','','','0','0','0','music','0');
INSERT INTO pw_categories VALUES ('41','0','0','酷站收藏','','','0','0','1','bookmark','0');
INSERT INTO pw_categories VALUES ('42','0','0','影视娱乐','','','0','0','1','bookmark','0');
INSERT INTO pw_categories VALUES ('43','0','0','体育运动','','','0','0','1','bookmark','0');
INSERT INTO pw_categories VALUES ('44','0','0','社会文化','','','0','0','1','bookmark','0');
INSERT INTO pw_categories VALUES ('45','0','0','科学技术','','','0','0','1','bookmark','0');
INSERT INTO pw_categories VALUES ('46','0','0','财经证券','','','0','0','1','bookmark','0');
INSERT INTO pw_categories VALUES ('47','0','0','国外网站','','','0','0','1','bookmark','0');
INSERT INTO pw_categories VALUES ('48','0','0','动漫卡通','','','0','0','1','bookmark','0');
INSERT INTO pw_categories VALUES ('49','0','0','文学资讯','','','0','0','1','bookmark','0');
INSERT INTO pw_categories VALUES ('50','0','0','影视音乐','','','0','0','1','team','0');
INSERT INTO pw_categories VALUES ('51','0','0','置业安居','','','0','0','1','team','0');
INSERT INTO pw_categories VALUES ('52','0','0','车行天下','','','0','0','1','team','0');
INSERT INTO pw_categories VALUES ('53','0','0','星座情缘','','','0','0','1','team','0');
INSERT INTO pw_categories VALUES ('54','0','0','体育联盟','','','0','0','0','team','0');
INSERT INTO pw_categories VALUES ('55','0','0','职业交流','','','0','0','0','team','0');
INSERT INTO pw_categories VALUES ('56','0','0','技术联盟','','','0','0','0','team','0');
INSERT INTO pw_categories VALUES ('57','0','0','同城对碰','','','0','0','0','team','0');
INSERT INTO pw_categories VALUES ('58','0','0','生活休闲','','','0','0','0','team','0');
INSERT INTO pw_categories VALUES ('59','0','0','商业金融','','','0','0','0','team','0');
INSERT INTO pw_categories VALUES ('60','0','0','原创空间','','','0','0','0','team','0');

INSERT INTO pw_group VALUES ('1','default','default','8','0','0','a:27:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"0";s:11:"allowupface";s:1:"0";s:8:"ifexport";s:1:"0";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"0";s:6:"msgmax";s:1:"0";s:10:"maxsendmsg";s:1:"0";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"0";s:9:"allowdown";s:1:"0";s:9:"uploadnum";s:1:"0";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:4:"1024";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:3:"0,0";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('2','default','guest','8','0','0','a:27:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"0";s:11:"allowupface";s:1:"0";s:8:"ifexport";s:1:"0";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"0";s:6:"msgmax";s:1:"0";s:10:"maxsendmsg";s:1:"0";s:9:"allowpost";s:1:"0";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"1";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"0";s:9:"allowdown";s:1:"0";s:9:"uploadnum";s:1:"0";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:1:"0";s:9:"attachext";s:0:"";s:6:"module";s:0:"";s:8:"upfacewh";s:3:"0,0";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('3', 'system', '管理员', '3', 0, 0, 'a:34:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"2";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"2";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:3:"100";s:10:"maxsendmsg";s:1:"0";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"1";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:1:"0";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:1:"0";s:9:"attachext";s:0:"";s:10:"allowlimit";s:1:"0";s:6:"module";s:25:"blog,photo,bookmark,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";s:7:"deluser";s:1:"1";s:6:"delatc";s:1:"1";s:6:"delcmt";s:1:"1";s:9:"delattach";s:1:"1";s:7:"cmduser";s:1:"1";s:6:"cmdact";s:1:"1";}', 1);
INSERT INTO pw_group VALUES ('4','system','总版主','4','0','0','a:34:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"2";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"2";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:3:"100";s:10:"maxsendmsg";s:2:"50";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:2:"50";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:5:"10240";s:9:"attachext";s:0:"";s:6:"module";s:25:"blog,photo,bookmark,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";s:10:"allowlimit";s:1:"0";s:7:"deluser";s:1:"0";s:6:"delatc";s:1:"0";s:6:"delcmt";s:1:"0";s:9:"delattach";s:1:"0";s:7:"cmduser";s:1:"0";s:6:"cmdact";s:1:"0";}','0');
INSERT INTO pw_group VALUES ('5','system','论坛版主','5','0','0','a:34:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"2";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"2";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:2:"50";s:10:"maxsendmsg";s:2:"50";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:2:"50";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:4:"5120";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";s:10:"allowlimit";s:1:"0";s:7:"deluser";s:1:"0";s:6:"delatc";s:1:"0";s:6:"delcmt";s:1:"0";s:9:"delattach";s:1:"0";s:7:"cmduser";s:1:"0";s:6:"cmdact";s:1:"0";}','0');
INSERT INTO pw_group VALUES ('6','system','禁止发言','8','0','0','a:34:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"0";s:11:"allowupface";s:1:"0";s:8:"ifexport";s:1:"0";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"0";s:6:"msgmax";s:1:"0";s:10:"maxsendmsg";s:1:"0";s:9:"allowpost";s:1:"0";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"0";s:9:"allowdown";s:1:"0";s:9:"uploadnum";s:1:"0";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:3:"512";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:3:"0,0";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";s:10:"allowlimit";s:1:"0";s:7:"deluser";s:1:"0";s:6:"delatc";s:1:"0";s:6:"delcmt";s:1:"0";s:9:"delattach";s:1:"0";s:7:"cmduser";s:1:"0";s:6:"cmdact";s:1:"0";}','0');
INSERT INTO pw_group VALUES ('7','system','未验证会员','8','0','0','a:34:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"0";s:11:"allowupface";s:1:"0";s:8:"ifexport";s:1:"0";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"0";s:6:"msgmax";s:1:"0";s:10:"maxsendmsg";s:1:"0";s:9:"allowpost";s:1:"0";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"0";s:9:"allowdown";s:1:"0";s:9:"uploadnum";s:1:"0";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:1:"1";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:3:"0,0";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";s:10:"allowlimit";s:1:"0";s:7:"deluser";s:1:"0";s:6:"delatc";s:1:"0";s:6:"delcmt";s:1:"0";s:9:"delattach";s:1:"0";s:7:"cmduser";s:1:"0";s:6:"cmdact";s:1:"0";}','0');
INSERT INTO pw_group VALUES ('8','member','初来乍到','8','0','0','a:27:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"1";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"0";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:2:"10";s:10:"maxsendmsg";s:2:"10";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:2:"10";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:4:"1024";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('9','member','一星会员','9','100','0','a:27:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"1";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:2:"10";s:10:"maxsendmsg";s:2:"20";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:2:"20";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:4:"2048";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('10','member','二星会员','10','300','0','a:27:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"0";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"0";s:6:"msgmax";s:2:"10";s:10:"maxsendmsg";s:2:"30";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:2:"30";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:4:"3072";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('11','member','三星会员','11','600','0','a:27:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:2:"10";s:10:"maxsendmsg";s:2:"40";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:2:"40";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:4:"4096";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('12','member','四星会员','12','1000','0','a:27:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:2:"20";s:10:"maxsendmsg";s:2:"50";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:2:"50";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:4:"5120";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('13','member','五星会员','13','5000','0','a:27:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:2:"20";s:10:"maxsendmsg";s:2:"60";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:2:"60";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:4:"6144";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('14','member','六星会员','14','10000','0','a:27:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"0";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:2:"20";s:10:"maxsendmsg";s:2:"70";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:2:"70";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:4:"7168";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('15','member','PW 终极会员','15','50000','0','a:27:{s:9:"allowread";s:1:"0";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"2";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:3:"100";s:10:"maxsendmsg";s:3:"100";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:3:"100";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:5:"10240";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";}','0');
INSERT INTO pw_group VALUES ('16','special','荣誉会员','5','0','0','a:34:{s:9:"allowread";s:1:"1";s:11:"allowsearch";s:1:"0";s:10:"searchtime";s:1:"0";s:8:"ifdomain";s:1:"2";s:12:"allowportait";s:1:"1";s:11:"allowupface";s:1:"1";s:8:"ifexport";s:1:"1";s:7:"intrnum";s:1:"0";s:7:"signnum";s:1:"0";s:8:"allowmsg";s:1:"1";s:6:"msgmax";s:3:"100";s:10:"maxsendmsg";s:3:"100";s:9:"allowpost";s:1:"1";s:8:"closecmt";s:1:"0";s:10:"closegbook";s:1:"0";s:8:"htmlcode";s:1:"0";s:11:"keywordlink";s:1:"1";s:11:"allowupload";s:1:"1";s:9:"allowdown";s:1:"1";s:9:"uploadnum";s:3:"100";s:10:"attachsize";s:1:"0";s:10:"uploadsize";s:5:"10240";s:9:"attachext";s:0:"";s:6:"module";s:16:"blog,photo,music";s:8:"upfacewh";s:7:"300,300";s:7:"postnum";s:5:"0,0,0";s:8:"limitnum";s:5:"0,0,0";s:10:"allowlimit";s:1:"0";s:7:"deluser";s:1:"0";s:6:"delatc";s:1:"0";s:6:"delcmt";s:1:"0";s:9:"delattach";s:1:"0";s:7:"cmduser";s:1:"0";s:6:"cmdact";s:1:"0";}','0');

INSERT INTO pw_hobby VALUES ('1','喜欢的音乐类型','0');
INSERT INTO pw_hobby VALUES ('3','喜欢的体育项目','0');
INSERT INTO pw_hobby VALUES ('4','喜欢的电影','0');
INSERT INTO pw_hobby VALUES ('5','喜欢的电视类型','0');
INSERT INTO pw_hobby VALUES ('6','喜欢的书籍类型','0');
INSERT INTO pw_hobby VALUES ('7','最喜欢的颜色','0');
INSERT INTO pw_hobby VALUES ('8','喜欢的美食','0');
INSERT INTO pw_hobby VALUES ('9','喜欢的宠物','0');
INSERT INTO pw_hobby VALUES ('10','喜欢的游戏','0');
INSERT INTO pw_hobby VALUES ('11','喜欢的娱乐类型','0');
INSERT INTO pw_hobby VALUES ('12','喜欢的歌星','0');
INSERT INTO pw_hobby VALUES ('13','喜欢的影星','0');
INSERT INTO pw_hobby VALUES ('14','喜欢的球星','0');
INSERT INTO pw_hobby VALUES ('15','喜欢的作者','0');

INSERT INTO pw_hobbyitem VALUES ('2','1','摇滚','0','1');
INSERT INTO pw_hobbyitem VALUES ('4','4','死神来了','0','1');
INSERT INTO pw_hobbyitem VALUES ('9','4','咒怨','0','1');
INSERT INTO pw_hobbyitem VALUES ('10','4','黑色星期五','0','1');
INSERT INTO pw_hobbyitem VALUES ('11','15','郭敬明','0','1');
INSERT INTO pw_hobbyitem VALUES ('12','15','韩寒','0','1');
INSERT INTO pw_hobbyitem VALUES ('13','15','易中天','0','1');
INSERT INTO pw_hobbyitem VALUES ('14','15','金庸','0','1');
INSERT INTO pw_hobbyitem VALUES ('15','15','安妮宝贝','0','1');
INSERT INTO pw_hobbyitem VALUES ('17','15','徐志摩','0','1');
INSERT INTO pw_hobbyitem VALUES ('18','15','沧月','0','1');
INSERT INTO pw_hobbyitem VALUES ('19','15','尼采','0','1');
INSERT INTO pw_hobbyitem VALUES ('20','15','王朔','0','1');
INSERT INTO pw_hobbyitem VALUES ('21','15','琼瑶','0','1');
INSERT INTO pw_hobbyitem VALUES ('22','15','明晓溪','0','1');
INSERT INTO pw_hobbyitem VALUES ('23','15','纪连海','0','1');
INSERT INTO pw_hobbyitem VALUES ('24','15','可爱淘','0','1');
INSERT INTO pw_hobbyitem VALUES ('25','15','落落','0','1');
INSERT INTO pw_hobbyitem VALUES ('26','15','亦舒','0','1');
INSERT INTO pw_hobbyitem VALUES ('27','14','贝克汉姆','0','1');
INSERT INTO pw_hobbyitem VALUES ('28','14','小小罗','0','1');
INSERT INTO pw_hobbyitem VALUES ('29','14','科比','0','1');
INSERT INTO pw_hobbyitem VALUES ('30','14','麦迪','0','1');
INSERT INTO pw_hobbyitem VALUES ('31','14','欧文','0','1');
INSERT INTO pw_hobbyitem VALUES ('32','14','卡卡','0','1');
INSERT INTO pw_hobbyitem VALUES ('33','14','罗纳尔多','0','1');
INSERT INTO pw_hobbyitem VALUES ('34','14','齐达内','0','1');
INSERT INTO pw_hobbyitem VALUES ('35','14','姚明','0','1');
INSERT INTO pw_hobbyitem VALUES ('36','14','巴拉克','0','1');
INSERT INTO pw_hobbyitem VALUES ('37','14','因扎吉','0','1');
INSERT INTO pw_hobbyitem VALUES ('38','14','阿伦·艾弗森','0','1');
INSERT INTO pw_hobbyitem VALUES ('39','14','斯蒂文·杰拉德','0','1');
INSERT INTO pw_hobbyitem VALUES ('40','14','易建联','0','1');
INSERT INTO pw_hobbyitem VALUES ('41','14','劳尔','0','1');
INSERT INTO pw_hobbyitem VALUES ('42','13','刘亦菲','0','1');
INSERT INTO pw_hobbyitem VALUES ('43','13','刘德华','0','1');
INSERT INTO pw_hobbyitem VALUES ('44','13','明道','0','1');
INSERT INTO pw_hobbyitem VALUES ('45','13','郑元畅','0','1');
INSERT INTO pw_hobbyitem VALUES ('46','13','林峰','0','1');
INSERT INTO pw_hobbyitem VALUES ('47','13','言承旭','0','1');
INSERT INTO pw_hobbyitem VALUES ('48','13','林依晨','0','1');
INSERT INTO pw_hobbyitem VALUES ('49','13','张柏芝','0','1');
INSERT INTO pw_hobbyitem VALUES ('50','13','周渝民','0','1');
INSERT INTO pw_hobbyitem VALUES ('51','13','山下智久','0','1');
INSERT INTO pw_hobbyitem VALUES ('52','13','李俊基','0','1');
INSERT INTO pw_hobbyitem VALUES ('53','13','龟梨和也','0','1');
INSERT INTO pw_hobbyitem VALUES ('54','13','赤西仁','0','1');
INSERT INTO pw_hobbyitem VALUES ('55','13','金正勋','0','1');
INSERT INTO pw_hobbyitem VALUES ('56','13','金希澈','0','1');
INSERT INTO pw_hobbyitem VALUES ('57','12','周杰伦','0','1');
INSERT INTO pw_hobbyitem VALUES ('58','12','蔡依林','0','1');
INSERT INTO pw_hobbyitem VALUES ('59','12','林俊杰','0','1');
INSERT INTO pw_hobbyitem VALUES ('60','12','s.h.e','0','1');
INSERT INTO pw_hobbyitem VALUES ('61','12','神话','0','1');
INSERT INTO pw_hobbyitem VALUES ('62','12','东方神起','0','1');
INSERT INTO pw_hobbyitem VALUES ('63','12','Ｒain','0','1');
INSERT INTO pw_hobbyitem VALUES ('64','12','韩庚','0','1');
INSERT INTO pw_hobbyitem VALUES ('65','12','ss501','0','1');
INSERT INTO pw_hobbyitem VALUES ('66','12','W-inds','0','1');
INSERT INTO pw_hobbyitem VALUES ('67','12','superjunior','0','1');
INSERT INTO pw_hobbyitem VALUES ('68','12','滨崎步','0','1');
INSERT INTO pw_hobbyitem VALUES ('69','12','布兰妮','0','1');
INSERT INTO pw_hobbyitem VALUES ('70','12','艾薇儿','0','1');
INSERT INTO pw_hobbyitem VALUES ('71','12','维多利亚','0','1');
INSERT INTO pw_hobbyitem VALUES ('72','11','唱KTV','0','1');
INSERT INTO pw_hobbyitem VALUES ('73','11','杀人游戏','0','1');
INSERT INTO pw_hobbyitem VALUES ('75','10','跑跑卡丁车','0','1');
INSERT INTO pw_hobbyitem VALUES ('76','10','魔兽争霸','0','1');
INSERT INTO pw_hobbyitem VALUES ('77','9','植物','0','1');
INSERT INTO pw_hobbyitem VALUES ('78','9','昆虫','0','1');
INSERT INTO pw_hobbyitem VALUES ('79','9','狗','0','1');
INSERT INTO pw_hobbyitem VALUES ('80','8','火锅','0','1');
INSERT INTO pw_hobbyitem VALUES ('81','8','川菜','0','1');
INSERT INTO pw_hobbyitem VALUES ('82','8','粤菜','0','1');
INSERT INTO pw_hobbyitem VALUES ('83','8','东北菜','0','1');
INSERT INTO pw_hobbyitem VALUES ('84','7','红色','0','1');
INSERT INTO pw_hobbyitem VALUES ('85','7','银色','0','1');
INSERT INTO pw_hobbyitem VALUES ('86','15','紫色','0','1');
INSERT INTO pw_hobbyitem VALUES ('87','6','空速星痕','0','1');
INSERT INTO pw_hobbyitem VALUES ('88','6','三宫六院七十二妃','0','1');
INSERT INTO pw_hobbyitem VALUES ('89','6','大明星爱上我','0','1');
INSERT INTO pw_hobbyitem VALUES ('90','5','空速星痕','0','1');
INSERT INTO pw_hobbyitem VALUES ('91','5','《武林外传》','0','1');
INSERT INTO pw_hobbyitem VALUES ('92','5','《爱杀17》','0','1');
INSERT INTO pw_hobbyitem VALUES ('94','4','《加勒比海盗》','0','1');
INSERT INTO pw_hobbyitem VALUES ('95','1','古典音乐','0','1');
INSERT INTO pw_hobbyitem VALUES ('96','1','Jpop/Kpop','0','1');
INSERT INTO pw_hobbyitem VALUES ('97','1','HIP-HOP音乐','0','1');
INSERT INTO pw_hobbyitem VALUES ('98','1','乡村音乐','0','1');
INSERT INTO pw_hobbyitem VALUES ('99','1','节奏布鲁斯','0','1');
INSERT INTO pw_hobbyitem VALUES ('100','1','福音歌曲','0','1');
INSERT INTO pw_hobbyitem VALUES ('101','7','黄色','0','1');
INSERT INTO pw_hobbyitem VALUES ('102','3','足球','0','1');
INSERT INTO pw_hobbyitem VALUES ('103','3','篮球','0','1');
INSERT INTO pw_hobbyitem VALUES ('104','7','黑色','0','1');
INSERT INTO pw_hobbyitem VALUES ('105','3','街舞','0','1');
INSERT INTO pw_hobbyitem VALUES ('106','7','白色','0','1');
INSERT INTO pw_hobbyitem VALUES ('107','3','瑜伽','0','1');
INSERT INTO pw_hobbyitem VALUES ('108','3','游泳','0','1');
INSERT INTO pw_hobbyitem VALUES ('116','9','牛','0','1');
INSERT INTO pw_hobbyitem VALUES ('110','11','心理测试','0','1');
INSERT INTO pw_hobbyitem VALUES ('111','11','聊天','0','1');
INSERT INTO pw_hobbyitem VALUES ('112','10','魔兽世界','0','1');
INSERT INTO pw_hobbyitem VALUES ('113','10','仙剑奇侠传','0','1');
INSERT INTO pw_hobbyitem VALUES ('114','9','猫','0','1');
INSERT INTO pw_hobbyitem VALUES ('115','9','鸟','0','1');
INSERT INTO pw_hobbyitem VALUES ('117','9','猴','0','1');
INSERT INTO pw_hobbyitem VALUES ('118','9','鸡','0','1');
INSERT INTO pw_hobbyitem VALUES ('119','8','过期海鲜','0','1');
INSERT INTO pw_hobbyitem VALUES ('120','7','大酱色','0','1');
INSERT INTO pw_hobbyitem VALUES ('121','3','柔道','0','1');
INSERT INTO pw_hobbyitem VALUES ('122','3','摔跤','0','1');
INSERT INTO pw_hobbyitem VALUES ('123','4','《大内刺客》','0','1');
INSERT INTO pw_hobbyitem VALUES ('125','5','焦点访谈','0','1');

INSERT INTO pw_itemnav VALUES ('1','首　页','index.php','0','0','0','head');
INSERT INTO pw_itemnav VALUES ('2','朋友圈','team.php','1','0','0','head');
INSERT INTO pw_itemnav VALUES ('4','搜　索','search.php','3','0','0','head');
INSERT INTO pw_itemnav VALUES ('3','同城博客','member.php','2','0','0','head');
INSERT INTO pw_itemnav VALUES ('5','帮　助','faq.php','4','0','1','head');
INSERT INTO pw_itemnav VALUES ('6','无图版','simple/index.php','5','0','1','foot');

INSERT INTO pw_module VALUES (1,'IMGPLAYER',0,'图片播放器',1,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (2,'ATCUSERS',0,'写手排行',2,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (3,'NEWUSERS',0,'最新加入',3,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (4,'CMDUSERS',0,'推荐博客',4,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (5,'NEWREPLIES',0,'新评文章',5,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (6,'HOTREPLIES',0,'热评文章',4,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (7,'HOTHITS',0,'热门文章',3,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (8,'USERFPRINTS',0,'脚印排行',2,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (9,'CLASSATCS',0,'分类排行',1,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (10,'ALLTAGS',0,'标签(Tags)',2,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (11,'NEWBLOGS',0,'最新文章',3,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (12,'NEWPHOTOS',0,'最新相册',4,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (13,'NEWMUSICS',0,'最新音乐',5,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (14,'NEWBOOKMARKS',0,'最新书签',4,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (15,'TEAMCLASS',0,'朋友圈分类',1,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (16,'TEAMATCS',0,'朋友圈排行',2,0,10,'25,0','1','');
INSERT INTO pw_module VALUES (17,'RANDOMLIST',0,'随机访问列表',2,0,10,'25,0','1','');

INSERT INTO pw_rightset VALUES ('3','a:67:{s:11:\"setting_set\";s:1:\"1\";s:12:\"setting_core\";s:1:\"1\";s:12:\"setting_code\";s:1:\"1\";s:12:\"setting_meta\";s:1:\"1\";s:9:\"setnav_cp\";s:1:\"1\";s:10:\"setnav_ort\";s:1:\"1\";s:10:\"setreg_reg\";s:1:\"1\";s:12:\"setreg_login\";s:1:\"1\";s:10:\"setdir_dir\";s:1:\"1\";s:11:\"setdir_html\";s:1:\"1\";s:12:\"setpost_post\";s:1:\"1\";s:11:\"setpost_att\";s:1:\"1\";s:12:\"setpost_mini\";s:1:\"1\";s:12:\"setpost_code\";s:1:\"1\";s:14:\"setpost_credit\";s:1:\"1\";s:12:\"setpost_ajax\";s:1:\"1\";s:19:\"setcombine_passport\";s:1:\"1\";s:21:\"setcombine_bbscombine\";s:1:\"1\";s:6:\"usercp\";s:1:\"1\";s:7:\"userort\";s:1:\"1\";s:14:\"setgroup_stats\";s:1:\"1\";s:14:\"setgroup_level\";s:1:\"1\";s:11:\"catecp_blog\";s:1:\"1\";s:12:\"catecp_photo\";s:1:\"1\";s:12:\"catecp_music\";s:1:\"1\";s:12:\"catecp_goods\";s:1:\"1\";s:11:\"catecp_file\";s:1:\"1\";s:15:\"catecp_bookmark\";s:1:\"1\";s:11:\"catecp_user\";s:1:\"1\";s:11:\"catecp_team\";s:1:\"1\";s:12:\"cateatc_blog\";s:1:\"1\";s:13:\"cateatc_photo\";s:1:\"1\";s:13:\"cateatc_music\";s:1:\"1\";s:13:\"cateatc_goods\";s:1:\"1\";s:12:\"cateatc_file\";s:1:\"1\";s:16:\"cateatc_bookmark\";s:1:\"1\";s:13:\"cateatc_gbook\";s:1:\"1\";s:12:\"catecmt_blog\";s:1:\"1\";s:13:\"catecmt_photo\";s:1:\"1\";s:13:\"catecmt_music\";s:1:\"1\";s:13:\"catecmt_goods\";s:1:\"1\";s:12:\"catecmt_file\";s:1:\"1\";s:16:\"catecmt_bookmark\";s:1:\"1\";s:10:\"teamcp_set\";s:1:\"1\";s:11:\"teamcp_list\";s:1:\"1\";s:7:\"teamatc\";s:1:\"1\";s:9:\"setmodule\";s:1:\"1\";s:8:\"setcache\";s:1:\"1\";s:9:\"notice_cp\";s:1:\"1\";s:10:\"notice_ort\";s:1:\"1\";s:9:\"setadv_cp\";s:1:\"1\";s:10:\"setadv_ort\";s:1:\"1\";s:10:\"setvote_cp\";s:1:\"1\";s:11:\"setvote_ort\";s:1:\"1\";s:14:\"sethobby_class\";s:1:\"1\";s:11:\"sethobby_cp\";s:1:\"1\";s:6:\"settag\";s:1:\"1\";s:12:\"setstyle_sys\";s:1:\"1\";s:13:\"setstyle_user\";s:1:\"1\";s:12:\"setother_ads\";s:1:\"1\";s:13:\"setother_link\";s:1:\"1\";s:7:\"setword\";s:1:\"1\";s:13:\"setsafe_ipban\";s:1:\"1\";s:13:\"setsafe_other\";s:1:\"1\";s:14:\"bakdata_bakout\";s:1:\"1\";s:13:\"bakdata_bakin\";s:1:\"1\";s:14:\"bakdata_repair\";s:1:\"1\";}');
INSERT INTO pw_rightset VALUES ('4','a:40:{s:11:\"setting_set\";s:1:\"1\";s:12:\"setting_code\";s:1:\"1\";s:12:\"setting_meta\";s:1:\"1\";s:9:\"setnav_cp\";s:1:\"1\";s:10:\"setnav_ort\";s:1:\"1\";s:10:\"setreg_reg\";s:1:\"1\";s:12:\"setreg_login\";s:1:\"1\";s:12:\"setpost_post\";s:1:\"1\";s:12:\"setpost_mini\";s:1:\"1\";s:12:\"setpost_code\";s:1:\"1\";s:14:\"setpost_credit\";s:1:\"1\";s:12:\"setpost_ajax\";s:1:\"1\";s:14:\"setgroup_stats\";s:1:\"1\";s:11:\"catecp_blog\";s:1:\"1\";s:12:\"catecp_photo\";s:1:\"1\";s:12:\"catecp_music\";s:1:\"1\";s:12:\"cateatc_blog\";s:1:\"1\";s:13:\"cateatc_photo\";s:1:\"1\";s:13:\"cateatc_music\";s:1:\"1\";s:12:\"catecmt_blog\";s:1:\"1\";s:13:\"catecmt_photo\";s:1:\"1\";s:13:\"catecmt_music\";s:1:\"1\";s:10:\"teamcp_set\";s:1:\"1\";s:11:\"teamcp_list\";s:1:\"1\";s:7:\"teamatc\";s:1:\"1\";s:9:\"setmodule\";s:1:\"1\";s:8:\"setcache\";s:1:\"1\";s:9:\"notice_cp\";s:1:\"1\";s:10:\"notice_ort\";s:1:\"1\";s:9:\"setadv_cp\";s:1:\"1\";s:10:\"setadv_ort\";s:1:\"1\";s:10:\"setvote_cp\";s:1:\"1\";s:11:\"setvote_ort\";s:1:\"1\";s:14:\"sethobby_class\";s:1:\"1\";s:11:\"sethobby_cp\";s:1:\"1\";s:6:\"settag\";s:1:\"1\";s:12:\"setother_ads\";s:1:\"1\";s:13:\"setother_link\";s:1:\"1\";s:7:\"setword\";s:1:\"1\";s:13:\"setsafe_ipban\";s:1:\"1\";}');
INSERT INTO pw_rightset VALUES ('5','a:13:{s:12:\"setting_meta\";s:1:\"1\";s:11:\"catecp_blog\";s:1:\"1\";s:12:\"catecp_photo\";s:1:\"1\";s:12:\"catecp_music\";s:1:\"1\";s:12:\"cateatc_blog\";s:1:\"1\";s:13:\"cateatc_photo\";s:1:\"1\";s:13:\"cateatc_music\";s:1:\"1\";s:12:\"catecmt_blog\";s:1:\"1\";s:13:\"catecmt_photo\";s:1:\"1\";s:13:\"catecmt_music\";s:1:\"1\";s:14:\"sethobby_class\";s:1:\"1\";s:11:\"sethobby_cp\";s:1:\"1\";s:7:\"setword\";s:1:\"1\";}');
INSERT INTO pw_rightset VALUES ('16','a:29:{s:7:\"setting\";s:1:\"0\";s:6:\"setdir\";s:1:\"0\";s:6:\"setreg\";s:1:\"0\";s:6:\"sethtm\";s:1:\"0\";s:8:\"addclass\";s:1:\"0\";s:9:\"editclass\";s:1:\"0\";s:10:\"uniteclass\";s:1:\"0\";s:7:\"adduser\";s:1:\"0\";s:6:\"usercp\";s:1:\"0\";s:5:\"level\";s:1:\"0\";s:9:\"userstats\";s:1:\"0\";s:7:\"schblog\";s:1:\"0\";s:6:\"teamcp\";s:1:\"0\";s:8:\"teamblog\";s:1:\"0\";s:9:\"blogcheck\";s:1:\"0\";s:12:\"commentcheck\";s:1:\"0\";s:11:\"updatecache\";s:1:\"0\";s:10:\"attachment\";s:1:\"0\";s:11:\"attachstats\";s:1:\"0\";s:6:\"notice\";s:1:\"0\";s:6:\"bakout\";s:1:\"0\";s:5:\"bakin\";s:1:\"0\";s:6:\"repair\";s:1:\"0\";s:8:\"setstyle\";s:1:\"0\";s:8:\"userskin\";s:1:\"0\";s:5:\"ipban\";s:1:\"0\";s:7:\"replace\";s:1:\"0\";s:9:\"sharelink\";s:1:\"0\";s:9:\"admin_log\";s:1:\"0\";}');

INSERT INTO pw_setting VALUES ('db_blogifopen','1','');
INSERT INTO pw_setting VALUES ('db_whyblogclose','博客升级中... 请等候 15分钟...','');
INSERT INTO pw_setting VALUES ('db_debug','1','');
INSERT INTO pw_setting VALUES ('db_blogname','LxBlog','');
INSERT INTO pw_setting VALUES ('db_blogurl','','');
INSERT INTO pw_setting VALUES ('db_lp','0','');
INSERT INTO pw_setting VALUES ('db_obstart','1','');
INSERT INTO pw_setting VALUES ('db_footertime','1','');
INSERT INTO pw_setting VALUES ('db_charset','utf8','');
INSERT INTO pw_setting VALUES ('db_forcecharset','0','');
INSERT INTO pw_setting VALUES ('db_defaultstyle','wind','');
INSERT INTO pw_setting VALUES ('db_timedf','8','');
INSERT INTO pw_setting VALUES ('db_cvtime','0','');
INSERT INTO pw_setting VALUES ('db_datefm','Y-m-d H:i','');
INSERT INTO pw_setting VALUES ('db_ifjump','1','');
INSERT INTO pw_setting VALUES ('db_refreshtime','0','');
INSERT INTO pw_setting VALUES ('db_ifonlinetime','1','');
INSERT INTO pw_setting VALUES ('db_onlinetime','1800','');
INSERT INTO pw_setting VALUES ('db_userdomain','0','');
INSERT INTO pw_setting VALUES ('db_domain','','');
INSERT INTO pw_setting VALUES ('db_ckpath','/','');
INSERT INTO pw_setting VALUES ('rg_allowregister','1','');
INSERT INTO pw_setting VALUES ('rg_showpermit','1','');
INSERT INTO pw_setting VALUES ('rg_permit','<p>当您申请用户时，表示您已经同意遵守本规章，请您自觉遵守以下条款：</p><p> 一、不得利用本站危害国家安全、泄露国家秘密，不得侵犯国家社会集体的和公民的合法权益，不得利用本站制作、复制和传播下列信息：</p><ul class=\"list\" style=\"list-style:none\"><li>（一）煽动抗拒、破坏宪法和法律、行政法规实施的；</li><li>（二）煽动颠覆国家政权，推翻社会主义制度的；</li><li>（三）煽动分裂国家、破坏国家统一的；</li><li>（四）煽动民族仇恨、民族歧视，破坏民族团结的；</li><li>（五）捏造或者歪曲事实，散布谣言，扰乱社会秩序的；</li><li>（六）宣扬封建迷信、淫秽、色情、赌博、暴力、凶杀、恐怖、教唆犯罪的；</li><li>（七）公然侮辱他人或者捏造事实诽谤他人的，或者进行其他恶意攻击的；</li><li>（八）损害国家机关信誉的；</li><li>（九）其他违反宪法和法律行政法规的；</li><li>（十）进行商业广告行为的。</li></ul><p>二、互相尊重，对自己的言论和行为负责。</p><p>三、禁止在申请用户时使用相关本站的词汇，或是带有侮辱、毁谤、造谣类的或是有其含义的各种语言进行注册用户，否则我们会将其删除。</p><p>四、禁止以任何方式对本站进行各种破坏行为。</p>','');
INSERT INTO pw_setting VALUES ('rg_showdetail','1','');
INSERT INTO pw_setting VALUES ('rg_allowsameip','0','');
INSERT INTO pw_setting VALUES ('rg_banname','管理员,admin,版主','');
INSERT INTO pw_setting VALUES ('rg_ifcheck','0','');
INSERT INTO pw_setting VALUES ('rg_lower','0','');
INSERT INTO pw_setting VALUES ('rg_reglen','3	12','');
INSERT INTO pw_setting VALUES ('rg_regcredit','10	0','');
INSERT INTO pw_setting VALUES ('db_attachdir','0','');
INSERT INTO pw_setting VALUES ('db_hour','0','');
INSERT INTO pw_setting VALUES ('db_http','N','');
INSERT INTO pw_setting VALUES ('db_htmifopen','0','');
INSERT INTO pw_setting VALUES ('db_dir','.php?','');
INSERT INTO pw_setting VALUES ('db_ext','.html','');
INSERT INTO pw_setting VALUES ('db_blogcheck','0','');
INSERT INTO pw_setting VALUES ('db_commentcheck','0','');
INSERT INTO pw_setting VALUES ('db_transfer','1','');
INSERT INTO pw_setting VALUES ('db_copyctrl','1','');
INSERT INTO pw_setting VALUES ('db_lenlimit','100,3,50000','');
INSERT INTO pw_setting VALUES ('db_allowupload','1','');
INSERT INTO pw_setting VALUES ('db_uploadmaxsize','102400','');
INSERT INTO pw_setting VALUES ('db_uploadfiletype','rar zip gif jpg txt jpeg','');
INSERT INTO pw_setting VALUES ('db_thumbifopen','1','');
INSERT INTO pw_setting VALUES ('db_thumbwh','75	75','');
INSERT INTO pw_setting VALUES ('db_gdcheck','0	0	1	100	1	0	0','');
INSERT INTO pw_setting VALUES ('db_credit','10,2,0,10,2,0,10,2','');
INSERT INTO pw_setting VALUES ('db_teamifopen','1','');
INSERT INTO pw_setting VALUES ('db_teamname','圈子','');
INSERT INTO pw_setting VALUES ('db_teamlimit','4','');
INSERT INTO pw_setting VALUES ('db_teamgroups','','');
INSERT INTO pw_setting VALUES ('db_openitem[\'bookmark\']','0','');
INSERT INTO pw_setting VALUES ('db_perpage','20','');
INSERT INTO pw_setting VALUES ('db_openitem[\'file\']','0','');
INSERT INTO pw_setting VALUES ('db_openitem[\'music\']','0','');
INSERT INTO pw_setting VALUES ('db_openitem[\'goods\']','0','');
INSERT INTO pw_setting VALUES ('db_ipcheck','0','');
INSERT INTO pw_setting VALUES ('db_hash','?3@d#s$7^','');
INSERT INTO pw_setting VALUES ('db_domainlen','4	15','');
INSERT INTO pw_setting VALUES ('cd_post[\'ifcode\']','1','');
INSERT INTO pw_setting VALUES ('cd_post[\'times\']','20','');
INSERT INTO pw_setting VALUES ('cd_post[\'ifpic\']','1','');
INSERT INTO pw_setting VALUES ('cd_post[\'picwidth\']','300','');
INSERT INTO pw_setting VALUES ('cd_post[\'picheight\']','200','');
INSERT INTO pw_setting VALUES ('cd_post[\'size\']','3','');
INSERT INTO pw_setting VALUES ('cd_post[\'ifflash\']','0','');
INSERT INTO pw_setting VALUES ('cd_post[\'ifmpeg\']','1','');
INSERT INTO pw_setting VALUES ('cd_post[\'ififrame\']','0','');
INSERT INTO pw_setting VALUES ('cd_sign[\'ifcode\']','0','');
INSERT INTO pw_setting VALUES ('cd_sign[\'ifpic\']','0','');
INSERT INTO pw_setting VALUES ('cd_sign[\'ifflash\']','0','');
INSERT INTO pw_setting VALUES ('db_thumbcolor','','');
INSERT INTO pw_setting VALUES ('db_articleurl','1','');
INSERT INTO pw_setting VALUES ('db_pptifopen','0','');
INSERT INTO pw_setting VALUES ('db_pptkey','dj:NaZ.A5,hsAmqp','');
INSERT INTO pw_setting VALUES ('db_pptserverurl','','');
INSERT INTO pw_setting VALUES ('db_pptloginurl','login.php','');
INSERT INTO pw_setting VALUES ('db_pptloginouturl','login.php?action=quit','');
INSERT INTO pw_setting VALUES ('db_pptregurl','register.php','');
INSERT INTO pw_setting VALUES ('db_ppttype','server','');
INSERT INTO pw_setting VALUES ('db_pptcredit','','');
INSERT INTO pw_setting VALUES ('db_ppturls','','');
INSERT INTO pw_setting VALUES ('db_cbbbsopen','0','');
INSERT INTO pw_setting VALUES ('db_cbbbsurl','','');
INSERT INTO pw_setting VALUES ('db_cbbbsdbname','','');
INSERT INTO pw_setting VALUES ('db_cbbbspre','pw_','');
INSERT INTO pw_setting VALUES ('db_cbbbscharset','utf8','');
INSERT INTO pw_setting VALUES ('db_cbbbsimgdir','images','');
INSERT INTO pw_setting VALUES ('db_cbbbsattachdir','attachment','');
INSERT INTO pw_setting VALUES ('db_diy','setting_core,usercp,setgroup_level,setmodule','');
INSERT INTO pw_setting VALUES ('db_ceoconnect','www.phpwind.net','');
INSERT INTO pw_setting VALUES ('db_ceoemail','admin@admin.com','');
INSERT INTO pw_setting VALUES ('db_icp','','');
INSERT INTO pw_setting VALUES ('db_icpurl','','');
INSERT INTO pw_setting VALUES ('db_maxresult','500','');
INSERT INTO pw_setting VALUES ('db_uploadface','0','');
INSERT INTO pw_setting VALUES ('db_facesize','1000	150	150','');
INSERT INTO pw_setting VALUES ('db_opensch','','');
INSERT INTO pw_setting VALUES ('rg_unneeddb','blogtitle	domainname	gender	qq	msn	yahoo	site	city	bday	cid	style	signature	introduce','');
INSERT INTO pw_setting VALUES ('rg_needdb','','');
INSERT INTO pw_setting VALUES ('db_attachnum','5','');
INSERT INTO pw_setting VALUES ('db_post[\'times\']','20','');
INSERT INTO pw_setting VALUES ('db_post[\'ifcode\']','1','');
INSERT INTO pw_setting VALUES ('db_post[\'ifpic\']','1','');
INSERT INTO pw_setting VALUES ('db_post[\'picwidth\']','300','');
INSERT INTO pw_setting VALUES ('db_post[\'picheight\']','200','');
INSERT INTO pw_setting VALUES ('db_post[\'size\']','3','');
INSERT INTO pw_setting VALUES ('db_post[\'ifflash\']','0','');
INSERT INTO pw_setting VALUES ('db_post[\'ifmpeg\']','1','');
INSERT INTO pw_setting VALUES ('db_post[\'ififrame\']','0','');
INSERT INTO pw_setting VALUES ('db_sign[\'ifcode\']','0','');
INSERT INTO pw_setting VALUES ('db_sign[\'ifpic\']','0','');
INSERT INTO pw_setting VALUES ('db_sign[\'picwidth\']','0','');
INSERT INTO pw_setting VALUES ('db_sign[\'picheight\']','0','');
INSERT INTO pw_setting VALUES ('db_sign[\'size\']','0','');
INSERT INTO pw_setting VALUES ('db_sign[\'ifflash\']','0','');
INSERT INTO pw_setting VALUES ('db_metadata','@:wind:@php,mysql,zend,blog,lxblog,phpwind@:wind:@php,mysql,zend,blog,lxblog,phpwind','');
INSERT INTO pw_setting VALUES ('lg_logindb','','');
INSERT INTO pw_setting VALUES ('db_postcheck','0','');
INSERT INTO pw_setting VALUES ('db_charsetmod','N_charset_string','');
INSERT INTO pw_setting VALUES ('db_iconvpre','TRANSLIT','');
INSERT INTO pw_setting VALUES ('db_autoimg','0','');
INSERT INTO pw_setting VALUES ('db_defaultustyle','default','');
INSERT INTO pw_setting VALUES ('db_ads','0','');
INSERT INTO pw_setting VALUES ('db_ipban','','');
INSERT INTO pw_setting VALUES ('db_showcate','blog	photo	music','');
INSERT INTO pw_setting VALUES ('db_cateshow','bookmark','');
INSERT INTO pw_setting VALUES ('db_smileshownum', '8', '');
INSERT INTO pw_setting VALUES ('db_setform', '1', '');
INSERT INTO pw_setting VALUES ('db_qcheck', '1	1	100	0	1	1', '');
INSERT INTO pw_setting VALUES ('db_question', '', '');
INSERT INTO pw_setting VALUES ('db_answer', '', '');
INSERT INTO pw_setting VALUES ('db_setassociate', '1', '');
INSERT INTO pw_setting VALUES ('db_keywordlink', '1', '');
INSERT INTO pw_setting VALUES ('db_uploadphototype', 'gif jpg', '');
INSERT INTO pw_setting VALUES ('db_msgcfg', '1	1	1', '');
INSERT INTO pw_setting VALUES ('db_msgmax', '10', '');
INSERT INTO pw_setting VALUES ('db_msgmaxsend', '10', '');
INSERT INTO pw_setting VALUES ('db_cbbbbsfid', '', '');
INSERT INTO pw_setting VALUES ('db_jsifopen', '1', '');
INSERT INTO pw_setting VALUES ('db_bindurl', '', '');
INSERT INTO pw_setting VALUES ('db_ml[\'mailifopen\']','0','');
INSERT INTO pw_setting VALUES ('db_ml[\'mailmethod\']','1','');
INSERT INTO pw_setting VALUES ('db_ml[\'smtphost\']','smtp.163.com','');
INSERT INTO pw_setting VALUES ('db_ml[\'smtpport\']','25','');
INSERT INTO pw_setting VALUES ('db_ml[\'smtpfrom\']','','');
INSERT INTO pw_setting VALUES ('db_ml[\'smtpauth\']','1','');
INSERT INTO pw_setting VALUES ('db_ml[\'smtpuser\']','','');
INSERT INTO pw_setting VALUES ('db_ml[\'smtppass\']','','');
INSERT INTO pw_setting VALUES ('db_ml[\'smtphelo\']','','');
INSERT INTO pw_setting VALUES ('db_ml[\'smtpmxmailname\']','','');
INSERT INTO pw_setting VALUES ('db_ml[\'mxdns\']','','');
INSERT INTO pw_setting VALUES ('db_ml[\'mxdnsbak\']','','');

INSERT INTO pw_share VALUES ('1','0','LxBlog','http://www.lxblog.net','理想Blogs','logo.gif','1','0','0');

INSERT INTO pw_style VALUES ('1','wind','default','default','0','#FFFFFF','790','760','#FFFFFF','#FFFFFF','#FFFFFF','#FFFFFF','#FFFFFF','#FFFFFF','#FFFFFF');

INSERT INTO pw_smile (path,name,vieworder,type) VALUES ('default','默认','1','0');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('1.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('2.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('3.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('4.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('5.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('6.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('7.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('8.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('9.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('10.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('11.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('12.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('13.gif','0','1');
INSERT INTO pw_smile (path,vieworder,type) VALUES ('14.gif','0','1');