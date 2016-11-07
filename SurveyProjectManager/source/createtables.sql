/* Project */
CREATE TABLE project (
 id SERIAL NOT NULL,
 uuid VARCHAR(36) NOT NULL PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 title VARCHAR(255),
 beginning DATE,
 ending DATE,
 phase INT,
 introduction TEXT,
 cause TEXT,
 descriptions TEXT,
 created TIMESTAMP WITH TIME ZONE,
 created_by VARCHAR(255),
 faceimage BYTEA,
 faceimage_thumbnail BYTEA
);
COMMENT ON TABLE public.project IS 'This table defines generic information about the survey project.';
COMMENT ON COLUMN public.project.id IS 'This attribute defines identifer of the project, which is used in DBMS.';
COMMENT ON COLUMN public.project.uuid IS 'This attribute defines global unique id of the project.';
COMMENT ON COLUMN public.project.name IS 'This attribute defines the name of the project.';
COMMENT ON COLUMN public.project.title IS 'This attribute defines the title of the project.';
COMMENT ON COLUMN public.project.beginning IS 'This attribute defines the date of the project beginning.';
COMMENT ON COLUMN public.project.ending IS 'This attribute defines the date of the project ending.';
COMMENT ON COLUMN public.project.ending IS 'This attribute defines the phase of the project.';
COMMENT ON COLUMN public.project.introduction IS 'This attribute can be used for introduction of the survey report.';
COMMENT ON COLUMN public.project.descriptions IS 'This attribute can be used for describing additional information about the project.';


/* Organization */
CREATE TABLE organization (
 id SERIAL NOT NULL,
 uuid VARCHAR(36) NOT NULL PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 section VARCHAR(255),
 country VARCHAR(255),
 administrativearea VARCHAR(255),
 city VARCHAR(255),
 contact_address VARCHAR(255),
 zipcode VARCHAR(255),
 phone VARCHAR(255)
);
COMMENT ON TABLE public.organization IS 'This table defines generic information about a organization.';
COMMENT ON COLUMN public.organization.id IS 'This attribute defines identifer of the organization, which is used in DBMS.';
COMMENT ON COLUMN public.organization.uuid IS 'This attribute defines global unique id of the organization.';
COMMENT ON COLUMN public.organization.name IS 'This attribute defines the name of the organization.';
COMMENT ON COLUMN public.organization.section IS 'This attribute defines the section name of the organization.';
COMMENT ON COLUMN public.organization.country IS 'This attribute defines the country where the organization located. Defined by CI_Contact.';
COMMENT ON COLUMN public.organization.administrativearea IS 'This attribute defines the administrative area where the organization located. Defined by CI_Contact.';
COMMENT ON COLUMN public.organization.city IS 'This attribute defines the city where the organization located. Defined by CI_Contact.';
COMMENT ON COLUMN public.organization.contact_address IS 'This attribute defines the contact address where the organization located. Defined by CI_Contact.';
COMMENT ON COLUMN public.organization.zipcode IS 'This attribute defines the zip code where the organization located. Defined by CI_Contact..';
COMMENT ON COLUMN public.organization.phone IS 'This attribute defines the phone number of the organization. Defined by CI_Contact.';

/* Member */
CREATE TABLE member (
 id SERIAL NOT NULL,
 uuid VARCHAR(36) NOT NULL PRIMARY KEY,
 org_id VARCHAR(36) NOT NULL REFERENCES organization(uuid),
 avatar BYTEA,
 surname VARCHAR(255),
 firstname VARCHAR(255),
 birthday TIMESTAMP WITH TIME ZONE,
 country VARCHAR(255),
 administrativearea VARCHAR(255),
 city VARCHAR(255),
 contact_address VARCHAR(255),
 zipcode VARCHAR(255),
 email VARCHAR(255),
 phone VARCHAR(255),
 mobile_phone VARCHAR(255),
 apointment VARCHAR(255),
 username VARCHAR(255) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 usertype VARCHAR(255) NOT NULL
);
COMMENT ON TABLE public.member IS 'This table defines generic information about a member.';
COMMENT ON COLUMN public.member.id IS 'This attribute defines identifer of the organization, which is used in DBMS.';
COMMENT ON COLUMN public.member.uuid IS 'This attribute defines global unique id of the organization.';
COMMENT ON COLUMN public.member.country IS 'This attribute defines the country where the member living. Defined by CI_Contact.';
COMMENT ON COLUMN public.member.administrativearea IS 'This attribute defines the administrative area where the memver living. Defined by CI_Contact.';
COMMENT ON COLUMN public.member.city IS 'This attribute defines the city where the member living. Defined by CI_Contact.';
COMMENT ON COLUMN public.member.contact_address IS 'This attribute defines the contact address where the member living. Defined by CI_Contact.';
COMMENT ON COLUMN public.member.zipcode IS 'This attribute defines the zip code of the address. Defined by CI_Contact..';
COMMENT ON COLUMN public.member.phone IS 'This attribute defines the phone number of the member. Defined by CI_Contact.';
COMMENT ON COLUMN public.member.username IS 'This attribute defines the acount name of the member on the archiving system.';
COMMENT ON COLUMN public.member.password IS 'This attribute defines the member password for the archiving system.';
COMMENT ON COLUMN public.member.usertype IS 'This attribute defines the member types for the archiving system.';

/* Member role in the project */
CREATE TABLE role (
 uuid VARCHAR(36) NOT NULL PRIMARY KEY,
 prj_id VARCHAR(36) NOT NULL REFERENCES project(uuid),
 mem_id VARCHAR(36) NOT NULL REFERENCES member(uuid),
 beginning DATE,
 ending DATE,
 rolename VARCHAR(255),
 biography TEXT
);


CREATE TABLE Consolidation (
 id SERIAL NOT NULL,
 uuid VARCHAR(36) NOT NULL PRIMARY KEY,
 prj_id VARCHAR(36) NOT NULL REFERENCES project(uuid),
 name VARCHAR(255),
 faceimage BYTEA,
 faceimage_thumbnail BYTEA,
 geographic_name VARCHAR(255),
 geographic_extent geometry(Polygon,4612),
 represented_point geometry(Point,4612),
 estimated_area geometry(Polygon,4612),
 estimated_period_beginning DATE,
 estimated_period_ending DATE,
 descriptions TEXT
);





CREATE TABLE material (
 materialid INT NOT NULL,
 consolidationid INT NOT NULL,
 projectid INT NOT NULL,
 relating_to INT,
 name VARCHAR(255),
 beginning TIMESTAMP WITH TIME ZONE,
 ending TIMESTAMP WITH TIME ZONE,
 represented_point geometry(Point,4612),
 path geometry(MultiLineStringM,4612),
 area geometry(Polygon,4612),
 material_number VARCHAR(255),
 descriptions TEXT
);

ALTER TABLE material ADD CONSTRAINT PK_material PRIMARY KEY (materialid,consolidationid,projectid);


CREATE TABLE material_to_material (
 relating_to INT NOT NULL,
 consolidationid INT,
 related_from INT,
 relation_type VARCHAR(255),
 descriptions VARCHAR(255),
 projectid INT
);

ALTER TABLE material_to_material ADD CONSTRAINT PK_material_to_material PRIMARY KEY (relating_to);

CREATE TABLE report (
 reportid INT NOT NULL,
 projectid INT NOT NULL,
 name VARCHAR(255)
);

ALTER TABLE report ADD CONSTRAINT PK_report PRIMARY KEY (reportid,projectid);


CREATE TABLE surface (
 surfaceid VARCHAR(255) NOT NULL,
 materialid INT NOT NULL,
 consolidationid INT NOT NULL,
 projectid INT NOT NULL,
 geometric_space BYTEA
);

ALTER TABLE surface ADD CONSTRAINT PK_surface PRIMARY KEY (surfaceid,materialid,consolidationid,projectid);


CREATE TABLE equipments (
 equipmentsid INT NOT NULL,
 projectid INT NOT NULL,
 related_equipments INT,
 name VARCHAR(255),
 type_of_equipments VARCHAR(255),
 maker VARCHAR(255),
 model VARCHAR(255),
 serialnumber VARCHAR(255)
);

ALTER TABLE equipments ADD CONSTRAINT PK_equipments PRIMARY KEY (equipmentsid,projectid);


CREATE TABLE keywords (
 keywordsid INT NOT NULL,
 materialid INT NOT NULL,
 consolidationid INT NOT NULL,
 projectid INT NOT NULL,
 keyword VARCHAR(255)
);

ALTER TABLE keywords ADD CONSTRAINT PK_keywords PRIMARY KEY (keywordsid,materialid,consolidationid,projectid);





CREATE TABLE Subject (
 subjectid INT NOT NULL,
 surfaceid VARCHAR(255) NOT NULL,
 materialid INT NOT NULL,
 consolidationid INT NOT NULL,
 projectid INT NOT NULL,
 object_name VARCHAR(255),
 arrangement geometry(Polygon,4612),
 descriptions TEXT
);

ALTER TABLE Subject ADD CONSTRAINT PK_Subject PRIMARY KEY (subjectid,surfaceid,materialid,consolidationid,projectid);


CREATE TABLE device_specification (
 specid INT NOT NULL,
 equipmentsid INT NOT NULL,
 projectid INT NOT NULL,
 item VARCHAR(255),
 value VARCHAR(255)
);

ALTER TABLE device_specification ADD CONSTRAINT PK_device_specification PRIMARY KEY (specid,equipmentsid,projectid);



CREATE TABLE section (
 sectionid INT NOT NULL,
 reportid INT NOT NULL,
 projectid INT NOT NULL,
 modified_by INT NOT NULL,
 organizationid INT NOT NULL,
 order_number INT,
 section_name VARCHAR(255),
 body_text VARCHAR(255),
 date_created TIMESTAMP WITH TIME ZONE,
 date_modified TIMESTAMP WITH TIME ZONE
);

ALTER TABLE section ADD CONSTRAINT PK_section PRIMARY KEY (sectionid,reportid,projectid,modified_by,organizationid);


CREATE TABLE surveydiary (
 diaryid INT NOT NULL,
 projectid INT NOT NULL,
 organizationid INT,
 memberid INT,
 date_created TIMESTAMP WITH TIME ZONE,
 weather VARCHAR(255),
 tempurature REAL,
 humidity REAL,
 descriptions TEXT
);

ALTER TABLE surveydiary ADD CONSTRAINT PK_surveydiary PRIMARY KEY (diaryid,projectid);


CREATE TABLE file (
 fileid VARCHAR(255) NOT NULL,
 projectid INT NOT NULL,
 organizationid INT,
 registered_by INT,
 materialid INT NOT NULL,
 consolidationid INT NOT NULL,
 surfaceid VARCHAR(255) NOT NULL,
 subjectid INT NOT NULL,
 thumbnail BYTEA,
 filepath VARCHAR(255),
 filename VARCHAR(255),
 mimetype VARCHAR(255)
);

ALTER TABLE file ADD CONSTRAINT PK_file PRIMARY KEY (fileid,projectid);


ALTER TABLE material ADD CONSTRAINT FK_material_0 FOREIGN KEY (consolidationid,projectid) REFERENCES Consolidation (consolidationid,projectid);
ALTER TABLE material ADD CONSTRAINT FK_material_1 FOREIGN KEY (relating_to) REFERENCES material_to_material (relating_to);


ALTER TABLE material_to_material ADD CONSTRAINT FK_material_to_material_0 FOREIGN KEY (related_from,consolidationid,projectid) REFERENCES material (materialid,consolidationid,projectid);


ALTER TABLE report ADD CONSTRAINT FK_report_0 FOREIGN KEY (projectid) REFERENCES project (projectid);


ALTER TABLE surface ADD CONSTRAINT FK_surface_0 FOREIGN KEY (materialid,consolidationid,projectid) REFERENCES material (materialid,consolidationid,projectid);


ALTER TABLE Consolidation ADD CONSTRAINT FK_Consolidation_0 FOREIGN KEY (projectid) REFERENCES project (projectid);


ALTER TABLE equipments ADD CONSTRAINT FK_equipments_0 FOREIGN KEY (projectid) REFERENCES project (projectid);
ALTER TABLE equipments ADD CONSTRAINT FK_equipments_1 FOREIGN KEY (related_equipments,projectid) REFERENCES equipments (equipmentsid,projectid);


ALTER TABLE keywords ADD CONSTRAINT FK_keywords_0 FOREIGN KEY (materialid,consolidationid,projectid) REFERENCES material (materialid,consolidationid,projectid);


ALTER TABLE Subject ADD CONSTRAINT FK_Subject_0 FOREIGN KEY (surfaceid,materialid,consolidationid,projectid) REFERENCES surface (surfaceid,materialid,consolidationid,projectid);


ALTER TABLE device_specification ADD CONSTRAINT FK_device_specification_0 FOREIGN KEY (equipmentsid,projectid) REFERENCES equipments (equipmentsid,projectid);


ALTER TABLE member ADD CONSTRAINT FK_member_0 FOREIGN KEY (organizationid) REFERENCES organization (organizationid);


ALTER TABLE section ADD CONSTRAINT FK_section_0 FOREIGN KEY (reportid,projectid) REFERENCES report (reportid,projectid);
ALTER TABLE section ADD CONSTRAINT FK_section_1 FOREIGN KEY (modified_by,organizationid) REFERENCES member (memberid,organizationid);


ALTER TABLE surveydiary ADD CONSTRAINT FK_surveydiary_0 FOREIGN KEY (projectid) REFERENCES project (projectid);
ALTER TABLE surveydiary ADD CONSTRAINT FK_surveydiary_1 FOREIGN KEY (memberid,organizationid) REFERENCES member (memberid,organizationid);


ALTER TABLE file ADD CONSTRAINT FK_file_0 FOREIGN KEY (projectid) REFERENCES project (projectid);
ALTER TABLE file ADD CONSTRAINT FK_file_1 FOREIGN KEY (materialid,consolidationid,projectid) REFERENCES material (materialid,consolidationid,projectid);
ALTER TABLE file ADD CONSTRAINT FK_file_2 FOREIGN KEY (registered_by,organizationid) REFERENCES member (memberid,organizationid);
ALTER TABLE file ADD CONSTRAINT FK_file_3 FOREIGN KEY (surfaceid,materialid,consolidationid,projectid) REFERENCES surface (surfaceid,materialid,consolidationid,projectid);
ALTER TABLE file ADD CONSTRAINT FK_file_4 FOREIGN KEY (subjectid,surfaceid,materialid,consolidationid,projectid) REFERENCES Subject (subjectid,surfaceid,materialid,consolidationid,projectid);
ALTER TABLE file ADD CONSTRAINT FK_file_5 FOREIGN KEY (consolidationid,projectid) REFERENCES Consolidation (consolidationid,projectid);


