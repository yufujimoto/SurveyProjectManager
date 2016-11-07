------------------------------------------------------------------------------
-- CLASSES FOR SURVEY PROJECT.
------------------------------------------------------------------------------
CREATE TABLE project (
	projectid		serial NOT NULL,
	projectname		varchar(255),
	project_begin		date,
	project_end		date,
	phase			int4,
	cause			text,
	remarks			text,
	createdby 		integer,
	CONSTRAINT pk_project PRIMARY KEY (projectid)
) WITH OIDS;

CREATE TABLE report (
	summaryid		serial NOT NULL,
	projectid		int4,
	ordernum		int4,
	sectionname		varchar(255),
	bodytext		text,
	datecreated 		timestamp with time zone,
	datelastmodified 	timestamp with time zone,
	createdby 		integer,
	CONSTRAINT pk_summary PRIMARY KEY (summaryid),
	FOREIGN KEY (projectid) REFERENCES project (projectid)
) WITH OIDS;

CREATE TABLE organization (
	organizationid		serial NOT NULL,
	organizationname	varchar(50),
	sectionname		varchar(50),
	country			varchar(100),
	administrativearea	varchar(100),
	city			varchar(100),
	contactaddress		varchar(255),
	zipcode			varchar(10),
	phone			varchar(20),
	CONSTRAINT pk_organization PRIMARY KEY (organizationid)
) WITH OIDS;

CREATE TABLE projectmember (
	memberid		serial NOT NULL,
	organizationid		int4,
	avatar			bytea,
	surname			varchar(50),
	firstname		varchar(50),
	username		varchar(10),
	birthday		date,
	country			varchar(100),
	administrativearea	varchar(100),
	city			varchar(100),
	contactaddress		varchar(255),
	zipcode			varchar(10),
	email			varchar(100),
	phone			varchar(20),
	mobile			varchar(20),
	appointment		varchar(50),
	passwd			varchar(32),
	CONSTRAINT pk_member PRIMARY KEY (memberid),
	FOREIGN KEY (projectid) REFERENCES project (projectid),
	FOREIGN KEY (organizationid) REFERENCES organization (organizationid)
) WITH OIDS;

CREATE TABLE rel_project_member
(
  projectid 			integer NOT NULL,
  memberid 			integer NOT NULL,
  member_from 			date,
  member_to 			date,
  responsible_role 		character varying(20)
) WITH OIDS;

CREATE TABLE device (
	deviceid		serial NOT NULL,
	projectid		int4,
	name			character varying(100),
	devicetype 		character varying(100),
	maker			character varying(100),
	model			character varying(100),
	serialnumber		character varying(100),
	registeredby 		integer,
	combination		integer,
	CONSTRAINT pk_device PRIMARY KEY (deviceid),
	FOREIGN KEY (projectid) REFERENCES project (projectid)
) WITH OIDS;

CREATE TABLE devicespecifiedinfomation (
	devicespecid		serial NOT NULL,
	deviceid		int4,
	attributename		varchar(100),
	attributevalue		varchar(100),
	CONSTRAINT pk_devicespec PRIMARY KEY (devicespecid),
	FOREIGN KEY (deviceid) REFERENCES device (deviceid)
) WITH OIDS;

CREATE TABLE files (
	fileid			serial NOT NULL,
	originalname		varchar(255),
	projectid		int4,
	memberid		int4,
	filename		varchar(255),
	filesize		double precision,
	filetype		varchar(10),
	datecreate		date,
	datemodified		date,
	dateupload		date,
	summary			text,
	CONSTRAINT pk_files PRIMARY KEY (fileid),
	FOREIGN KEY (memberid) REFERENCES projectmember (memberid),
	FOREIGN KEY (projectid) REFERENCES project (projectid)
) WITH OIDS;

CREATE TABLE surveydiary (
	diaryid			serial NOT NULL,
	projectid		int4,
	memberid		int4,
	createddate		date,
	weather			varchar(100),
	tempurature		varchar(100),
	humidity		varchar(100),
	CONSTRAINT pk_surveydiary PRIMARY KEY (diaryid),
	FOREIGN KEY (memberid) REFERENCES projectmember (memberid),
	FOREIGN KEY (projectid) REFERENCES project (projectid)
) WITH OIDS;

------------------------------------------------------------------------------
-- CLASSES FOR SURVEY MATERIALS.
------------------------------------------------------------------------------
CREATE TABLE consolidation (
	consolidationid		serial NOT NULL,
	projectid		int4,
	number_of_materials	int4,
	consolidationname	varchar(255),
	remarks			text,
	CONSTRAINT pk_consolidation PRIMARY KEY (consolidationid),
	FOREIGN KEY (projectid) REFERENCES project (projectid)
) WITH OIDS;

CREATE TABLE material (
	materialid		serial NOT NULL,
	consolidationid		int4,
	material_number		varchar(255),
	keywords		varchar[],
	remarks			text,
	CONSTRAINT pk_material PRIMARY KEY (materialid),
	FOREIGN KEY (consolidationid) REFERENCES consolidation (consolidationid)
) WITH OIDS;

CREATE TABLE relation_of_materials (
	relmaterialsid		serial NOT NULL,
	related_from		int4,
	relating_to		int4,
	relation_type		varchar(255),
	remarks			varchar(255),
	CONSTRAINT pk_relation_of_materials PRIMARY KEY (relmaterialsid)
) WITH OIDS;

CREATE TABLE surface (
	surfaceid		serial NOT NULL,
	materialid		int4,
	CONSTRAINT pk_surface PRIMARY KEY (surfaceid),
	FOREIGN KEY (materialid) REFERENCES material (materialid)
);

CREATE TABLE denoted_subject (
	denotedsubjectid	serial NOT NULL,
	surfaceid		int4,
	object_name		varchar(255),
	arrangement_x		real[],
	arrangement_y		real[],
	CONSTRAINT pk_denoted_subject PRIMARY KEY (denotedsubjectid),
	FOREIGN KEY (surfaceid) REFERENCES surface (surfaceid)
);

CREATE TABLE digitized_image (
	digitizedimageid	serial not null,
	materialid		int4,
	filename		varchar,
	image			bytea,
	thumbnail		bytea,
	exif_orientation	varchar,
	exif_version		varchar,
	exif_imagewidth		integer,
	exif_imageheight	integer,
	exif_datetimeoriginal	timestamp,
	exif_datetimedigitized	timestamp,
	exif_datetime		timestamp,
	exif_make		varchar,
	exif_model		varchar,
	exif_fnumber		double precision,
	exif_focallength	double precision,
	exif_isospeedratings	integer,
	exif_exposuretime	varchar,
	exif_maxaperturevalue	varchar,
	exif_flash		varchar,
	exif_meteringmode	varchar,
	exif_lightsource	varchar,
	exif_exposureprogram	varchar,
	exif_colorspace		varchar,
	exif_ycbcrpositioning	varchar,
	exif_compesedbitsperpixel	 double precision,
	exif_xresolution	integer,
	exif_yresolution	integer,
	exif_resolutionunit	varchar,
	exif_gps_datestamp	timestamp,
	exif_gps_timestamp	timestamp,
	exif_gps_measuremode	varchar,
	exif_gps_mapdatum	varchar,
	exif_gps_dop		double precision,
	exif_gps_status		varchar,
	exif_gps_latitude	double precision,
	exif_gps_latituderef	varchar,
	exif_gps_longitude	double precision,
	exif_gps_longituderef	varchar,
	exif_gps_altitude	double precision,
	exif_gps_altituderef	varchar,
	exif_gps_imgdirection	double precision,
	exif_gps_imgdirectionref	varchar,
	exif_gps_speed		double precision,
	exif_gps_track		varchar,
	exif_gps_trackref	varchar,
	exif_gps_speedref	varchar,
	exif_gps_differential	varchar,
	CONSTRAINT pk_exif PRIMARY KEY (digitizedimageid),
	FOREIGN KEY (materialid) REFERENCES material (materialid)
);