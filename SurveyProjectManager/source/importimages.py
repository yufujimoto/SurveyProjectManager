import sys, os, getopt, psycopg2, argparse, inspect, numpy, zbar, math, csv, uuid
from PIL import Image, ImageDraw
from PIL.ExifTags import TAGS, GPSTAGS

def getExifData(filename):
    ret = {}
    image = Image.open(filename)
    info = image._getexif()
    ret["linkage"] = filename
    if info:
        for tag, value in info.items():
            decoded = TAGS.get(tag, tag)
            if decoded == "ExifVersion": ret[decoded] = "%s.%s.%s.%s" %(value[0], value[1], value[2], value[3])
            elif decoded == "ExifImageWidth": ret[decoded] = int(value)
            elif decoded == "ExifImageHeight": ret[decoded] = int(value)
            elif decoded == "XResolution": ret[decoded] = int(float(value[0])/float(value[1]))
            elif decoded == "YResolution": ret[decoded] = int(float(value[0])/float(value[1]))
            elif decoded == "ResolutionUnit": ret[decoded] = getResolutionUnit(int(value))
            elif decoded == "ColorSpace": ret[decoded] = getColorSpace(int(value))
            elif decoded == "Make": ret[decoded] = str(value)
            elif decoded == "Model": ret[decoded] = str(value)
            elif decoded == "LightSource": ret[decoded] = getLightSource(int(value))
            elif decoded == "Flash": ret[decoded] = getFlash(int(value))
            elif decoded == "MeteringMode": ret[decoded] = getMeteringMode(int(value))
            elif decoded == "ExposureProgram": ret[decoded] = getExposureProgram(int(value))
            elif decoded == "DateTimeDigitized": 
                value = str(value).split()
                ret[decoded] =  getGeneralDate(value[0])+" "+value[1]
            elif decoded == "DateTimeOriginal": 
                value = str(value).split()
                ret[decoded] =  getGeneralDate(value[0])+" "+value[1]
            elif decoded == "CompressedBitsPerPixel": ret[decoded] = float(value[0])/float(value[1])
            elif decoded == "FNumber": ret[decoded] = float(value[0])/float(value[1])
            elif decoded == "FocalLength": ret[decoded] = float(value[0])/float(value[1])
            elif decoded == "ISOSpeedRatings": ret[decoded] = int(value)
            elif decoded == "DateTime": 
                value = str(value).split()
                ret[decoded] =  getGeneralDate(value[0])+" "+value[1]
            elif decoded == "Orientation": ret[decoded] = getOrientation(value)
            elif decoded == "ExposureTime": ret[decoded] = str(value[0])+"/"+str(value[1])
            elif decoded == "MaxApertureValue": ret[decoded] = float(value[0])/float(value[1])
            elif decoded == "YCbCrPositioning": ret[decoded]= getYCbCrPositioning(int(value))
            elif decoded == "GPSInfo": ret = getGpsInfo(ret, value)
    else:
        print("There are no valid Exif Tags.")
    return ret

def _if_exist(data, key):
    if key in data:
        return(data[key])
    return None

def getLightSource(value):
    if value == 0 : return("Unknown")
    elif value == 1 : return("Daylight")
    elif value == 2 : return("Fluorescent")
    elif value == 3 : return('Tungsten ("incandescent)')
    elif value == 4 : return("Flash")
    elif value == 9 : return("Fine weather")
    elif value == 10 : return("Cloudy")
    elif value == 11 : return("Shade")
    elif value == 12 : return("Daylight fluorescent")
    elif value == 13 : return("Day white fluorescent")
    elif value == 14 : return("Cool white fluorescent")
    elif value == 15 : return("White fluorescent")
    elif value == 16 : return("Warm white fluorescent")
    elif value == 17 : return("Standard light a")
    elif value == 18 : return("Standard light b")
    elif value == 19 : return("Standard light c")
    elif value == 20 : return("D55")
    elif value == 21 : return("D65")
    elif value == 22 : return("D75")
    elif value == 23 : return("D50")
    elif value == 24 : return("ISO studio tungsten")
    elif value == 255 : return("Other")
    else: return("Not Defined")
    
    return(None)

def getResolutionUnit(value):
    if value == 1 : return("None")
    elif value == 2 : return("inches")
    elif value == 3 : return("cm")
    else: return("Not Defined")
    
    return(None)

def getExposureProgram(value):
    if value == 0 : return("Not Defined")
    elif value == 1 : return("Manual")
    elif value == 2 : return("Program AE")
    elif value == 3 : return("Aperture-priority AE")
    elif value == 4 : return("Shutter speed priority AE")
    elif value == 5 : return("Creative (Slow speed)")
    elif value == 6 : return("Action (High speed)")
    elif value == 7 : return("Portrait")
    elif value == 8 : return("Landscape")
    elif value == 9 : return("Bulb")
    else: return("Not Defined")
    
    return(None)

def getColorSpace(value):
    if value == 1 : return("sRGB")
    if value == 2 : return("Adobe RGB")
    if value == 65533 : return("Wide Gamut RGB")
    if value == 65534 : return("ICC Profile")
    if value == 65535 : return("Uncalibrated")
    
    return(None)

def getFlash(value):    
    if value == 0 : return("No Flash")
    elif value == 1 : return("Fired")
    elif value == 5 : return("Fired: Return not detected")
    elif value == 7 : return("Fired: Return detected")
    elif value == 8 : return("On: Did not fire")
    elif value == 9 : return("On: Fired")
    elif value == 13 : return("On: Return not detected")
    elif value == 15 : return("On: Return detected")
    elif value == 16 : return("Off: Did not fire")
    elif value == 20 : return("Off: Did not fire: Return not detected")
    elif value == 24 : return("Auto: Did not fire")
    elif value == 25 : return("Auto: Fired")
    elif value == 29 : return("Auto: Fired: Return not detected")
    elif value == 31 : return("Auto: Fired: Return detected")
    elif value == 32 : return("No flash function")
    elif value == 48 : return("Off: No flash function")
    elif value == 65 : return("Fired: Red-eye reduction")
    elif value == 69 : return("Fired: Red-eye reduction: Return not detected")
    elif value == 71 : return("Fired: Red-eye reduction: Return detected")
    elif value == 73 : return("On: Red-eye reduction")
    elif value == 77 : return("On: Red-eye reduction: Return not detected")
    elif value == 79 : return("On: Red-eye reduction: Return detected")
    elif value == 80 : return("Off: Red-eye reduction")
    elif value == 88 : return("Auto: Did not fire: Red-eye reduction")
    elif value == 89 : return("Auto: Fired: Red-eye reduction")
    elif value == 93 : return("Auto: Fired: Red-eye reduction: Return not detected")
    elif value == 95 : return("Auto: Fired: Red-eye reduction: Return detected")
    else: return("Not Defined")
    
    return(None)

def getMeteringMode(value):
    if value == 0 : return("Unknown")
    elif value == 1 : return("Average")
    elif value == 2 : return("Center-weighted average")
    elif value == 3 : return("Spot")
    elif value == 4 : return("Multi-spot")
    elif value == 5 : return("Multi-segment")
    elif value == 6 : return("Partial")
    elif value == 255 : return("Other")
    else: return("Not Defined")
    
    return(None)

def getOrientation(value):
    if value == 1 : return("Horizontal (normal)")
    if value == 2 : return("Mirror horizontal")
    if value == 3 : return("Rotate 180")
    if value == 4 : return("Mirror vertical")
    if value == 5 : return("Mirror horizontal and rotate 270 CW")
    if value == 6 : return("Rotate 90 CW")
    if value == 7 : return("Mirror horizontal and rotate 90 CW")
    if value == 8 : return("Rotate 270 CW")
    
    return(None)

def getYCbCrPositioning(value):
    if value == 1 : return("Centered")
    if value == 2 : return("Co-sited")
    
    return(None)

def getGeneralDate(value):
    return(value.replace(":","-"))

def getGpsInfo(decodedDict, values):
    gpsInfo = {}
    for tag in values:
        decoded = GPSTAGS.get(tag, tag)
        if decoded == "GPSVersionID": decodedDict[decoded] = getGpsVersionID(values[tag])
        elif decoded == "GPSLatitudeRef": decodedDict[decoded] = values[tag]
        elif decoded == "GPSLongitudeRef": decodedDict[decoded] = values[tag]
        elif decoded == "GPSAltitudeRef": decodedDict[decoded] = values[tag]
        elif decoded == "GPSSpeedRef": decodedDict[decoded] = getGpsSpeedRef(values[tag])
        elif decoded == "GPSTrackRef": decodedDict[decoded] = getGpsDirection(values[tag])
        elif decoded == "GPSTimeStamp": 
            value = getGpsTimeStamp(values[tag]).split()
            decodedDict[decoded] = getGeneralDate(value[0])+" "+value[1]
        elif decoded == "GPSStatus": decodedDict[decoded] = getGpsStatus(values[tag])
        elif decoded == "GPSMeasureMode": decodedDict[decoded] = getGpsMeasureMode(values[tag])
        elif decoded == "GPSSpeed": decodedDict[decoded] = float(values[tag][0])/float(values[tag][1])
        elif decoded == "GPSTrack": decodedDict[decoded] = float(values[tag][0])/float(values[tag][1]) 
        elif decoded == "GPSImgDirectionRef": decodedDict[decoded] = getGpsDirection(values)
        elif decoded == "GPSImgDirection": decodedDict[decoded] = float(values[tag][0])/float(values[tag][1]) 
        elif decoded == "GPSMapDatum": decodedDict[decoded] = values[tag]
        elif decoded == 29 : 
            value = values[tag]
            decodedDict["GPSDateStamp"] = getGeneralDate(value[0])+" "+value[1]
        elif decoded == 30 : decodedDict["GPSDifferential"] = getGpsDifferenctial(values[tag])
        else: decodedDict[decoded] = values[tag]
    
    # Extract Geographic location from Exif tags.
    lat = None; lon = None; alt = None
    
    exif_lat = _if_exist(values, 2)
    exif_lon = _if_exist(values, 4)
    alt = _if_exist(values, 6)
    
    lat_ref = _if_exist(values,1)
    lon_ref = _if_exist(values,3)
    alt_ref = _if_exist(values,5)
    
    # Get latitude and longitude.
    if exif_lat and lat_ref and exif_lon and lon_ref:
        lat = getDegree(exif_lat); 
        lon = getDegree(exif_lon)
        if lat_ref != "N" : lat = 0 - float(lat)
        if lon_ref != "E" : lon = 0 - float(lon)
        decodedDict["GPSLatitude"] = float(lat)
        decodedDict["GPSLongitude"] = float(lon)
    
    # Get Altitude.
    if alt and alt_ref:
        if alt_ref != 0 : 0 - (float(alt[0])/float(alt[1]))
        decodedDict["GPSAltitude"] = float(alt[0])/float(alt[1])
    
    # Get Dop Information.
    exif_dop = _if_exist(values, 11)
    dop_ref = _if_exist(values, 10)
    
    if exif_dop and dop_ref:
        if dop_ref == 2 : dop_label = "HDOP"
        elif dop_ref == 3 : dop_label = "PDOP"
        else: dop_label = "GPSDOP"
        value = float(exif_dop[0])/float(exif_dop[1])
        decodedDict[dop_label] = value
    return(decodedDict)
    return(None)

def getGpsVersionID(value):
    ret = ""
    v1 = str(value[0])
    v2 = str(value[1])
    v3 = str(value[2])
    v4 = str(value[3])
    ret = "%s.%s.%s.%s" % (v1, v2, v3, v4)
    return(ret)

def getGpsTimeStamp(value):
    ret = ""
    h = float(value[0][0]) / float(value[0][1])
    m = float(value[0][0]) / float(value[0][1])
    s = float(value[0][0]) / float(value[0][1])
    ret = "%02d:%02d:%02d" % (h, m, s)
    return(ret)

def getGpsStatus(value):
    if value == "A": return("Measurement is in progress")
    elif value == "V" : return("Measurement is in Interoperability")
    else: return("Not Available")

def getGpsMeasureMode(value):
    if value == 2 : return("2D")
    elif value == 3 : return("3D")
    else: return("Not Available")

def getGpsSpeedRef(value):
    if value == "K" : return("Km/h")
    elif value == "M" : return("Mile/h")
    elif value == "N" : return("Nots/h")
    else: return("Not Available")

def getGpsDirection(value):
    if value == "T" : return("True direction")
    elif value == "M" : return("Magnetic direction")
    else: return("Not Available")

def getGpsDifferenctial(value):
    if value == 0 : return("Measurement without differential correction")
    if value == 1 : return("Differential correction applied")

def getDegree(value):
    d = float(value[0][0])/float(value[0][1])
    m = float(value[1][0])/float(value[1][1])
    s = float(value[2][0])/float(value[2][1])
    ret = d + (m/60.0) + (s/3600.0)
    return(ret)

def readImage(filename):
    fin=""
    try:
        fin = open(filename,"rb")
        img = fin.read()
        return img
    except IOError, e:
        print "Error %d: %s" % (e.args[0],e.args[1])
        sys.exit(1)
    finally:
        if fin: fin.close()

def thumbnailImage(imgfile,output):
    basewidth=300
    img = Image.open(imgfile)
    scale = 0
    wsize = 0
    hsize = 0
    
    if img.size[0]>=img.size[1]:
        scale=(basewidth/float(img.size[0]))
        wsize = 300
        hsize = int((float(img.size[1])*float(scale)))
    else:
        scale=(basewidth/float(img.size[1]))
        wsize = int((float(img.size[0])*float(scale)))
        hsize = 300
    
    img = img.resize([wsize, hsize])
    img.save(output)


def main(dbname, user, passwd, csvfl):
    con = psycopg2.connect(database = dbname, user = user, password = passwd)
    con.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
    cur = con.cursor()
    
    with open(csvfl, 'rb') as csvfile:
        count = 1
        spamreader = csv.reader(csvfile, delimiter=',', quotechar='"')
        for row in spamreader:
            if count >= 100:
                con.commit()
                cur.execute("VACUUM public.digitized_image");
                
            dir_img = row[0]
            filenam = row[1]
            serial = row[2]
            matnum = row[3]
            dscrpt = row[4]
            
            jpgfile = os.path.join(os.path.join(dir_img,"jpg"),filenam)
            thmfile = os.path.join(os.path.join(dir_img,"thums"),filenam)
            
            # Create a thumbnail of original Image if not exists.
            if not os.path.exists(thmfile):
                thumbnailImage(jpgfile, thmfile)
            
            # Get uuid of the material.
            cur.execute("SELECT uuid FROM material where material_number='%s'" %(matnum)) 
            result = cur.fetchone()
            
            if result:
                mat_uuid = result[0]
                
                # Generate uuid for the digitized image.
                img_uuid = str(uuid.uuid4())
                
                # Convert jpeg image to bytea type.
                orgimg = psycopg2.Binary(readImage(jpgfile))
                thmimg = psycopg2.Binary(readImage(thmfile))
                
                # Create the insert statement.
                cols = "uuid, mat_id, filename, image, thumbnail, descriptions, exif_orientation, exif_version, exif_imagewidth, exif_imageheight, exif_datetimeoriginal, exif_datetimedigitized, exif_datetime, exif_make, exif_model, exif_fnumber, exif_focallength, exif_isospeedratings, exif_exposuretime, exif_maxaperturevalue, exif_flash, exif_meteringmode, exif_lightsource, exif_exposureprogram, exif_colorspace, exif_ycbcrpositioning, exif_compesedbitsperpixel, exif_xresolution, exif_yresolution, exif_resolutionunit, exif_gps_datestamp, exif_gps_timestamp, exif_gps_measuremode, exif_gps_mapdatum, exif_gps_dop, exif_gps_status, exif_gps_latitude, exif_gps_latituderef, exif_gps_longitude, exif_gps_longituderef, exif_gps_altitude, exif_gps_altituderef, exif_gps_imgdirection, exif_gps_imgdirectionref, exif_gps_speed, exif_gps_track, exif_gps_trackref, exif_gps_speedref, exif_gps_differential"
                s = "INSERT INTO digitized_image (%s) VALUES ('%s', '%s', '%s', %s, %s, '%s'," %(cols, img_uuid, mat_uuid, filenam, orgimg, thmimg, dscrpt)
                
                exif=getExifData(jpgfile)
                if _if_exist(exif, 'Orientation') != None: s = s + "'" + str(exif['Orientation']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'ExifVersion') != None: s = s + "'" + str(exif['ExifVersion']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'ExifImageWidth') != None: s = s + str(exif['ExifImageWidth']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'ExifImageHeight') != None: s = s + str(exif['ExifImageHeight']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'DateTimeOriginal') != None: s = s + "'" + str(exif['DateTimeOriginal']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'DateTimeDigitized') != None: s = s + "'" + str(exif['DateTimeDigitized']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'DateTime') != None: s = s + "'" + str(exif['DateTime']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'Make') != None: s = s + "'" + str(exif['Make']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'Model') != None: s = s + "'" + str(exif['Model']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'FNumber') != None: s = s + str(exif['FNumber']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'FocalLength') != None: s = s + str(exif['FocalLength']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'ISOSpeedRatings') != None: s = s + str(exif['ISOSpeedRatings']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'ExposureTime') != None: s = s + "'" + str(exif['ExposureTime']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'MaxApertureValue') != None: s = s + str(exif['MaxApertureValue']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'Flash') != None: s = s + "'" + str(exif['Flash']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'MeteringMode') != None: s = s + "'" + str(exif['MeteringMode']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'LightSource') != None: s = s + "'" + str(exif['LightSource']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'ExposureProgram') != None: s = s + "'" + str(exif['ExposureProgram']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'ColorSpace') != None: s = s + "'" + str(exif['ColorSpace']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'YCbCrPositioning') != None: s = s + "'" + str(exif['YCbCrPositioning']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'CompesedBitsPerPixel') != None: s = s + str(exif['CompesedBitsPerPixel']).strip(",").strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'XResolution') != None: s = s + str(exif['XResolution']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'YResolution') != None: s = s + str(exif['YResolution']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'ResolutionUnit') != None: s = s + "'" + str(exif['ResolutionUnit']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSDateStamp') != None: s = s + "'" + str(exif['GPSDateStamp']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSTimeStamp') != None: s = s + "'" + str(exif['GPSTimeStamp']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSMeasureMode') != None: s = s + "'" + str(exif['GPSMeasureMode']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSMapDatum') != None: s = s + "'" + str(exif['GPSMapDatum']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSDOP') != None: s = s + str(exif['GPSDOP']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSStatus') != None: s = s + "'" + str(exif['GPSStatus']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSLatitude') != None: s = s + str(exif['GPSLatitude']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSLatitudeRef') != None: s = s + "'" + str(exif['GPSLatitudeRef']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSLongitude') != None: s = s + str(exif['GPSLongitude']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSLongitudeRef') != None: s = s + "'" + str(exif['GPSLongitudeRef']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSAltitude') != None: s = s + str(exif['GPSAltitude']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSAltitudeRef') != None: s = s + "'" + str(exif['GPSAltitudeRef']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSImgDirection') != None: s = s + str(exif['GPSImgDirection']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSImgDirectionRef') != None: s = s + "'" + str(exif['GPSImgDirectionRef']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSSpeed') != None: s = s + str(exif['GPSSpeed']).strip() + str(",")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSTrack') != None: s = s + "'" + str(exif['GPSTrack']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSTrackRef') != None: s = s + "'" + str(exif['GPSTrackRef']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSSpeedRef') != None: s = s + "'" + str(exif['GPSSpeedRef']).strip() + str("',")
                else: s = s + "NULL,"
                if _if_exist(exif, 'GPSDifferential') != None: s = s + "'" + str(exif['GPSDifferential']).strip() + str("',")
                else: s = s + "NULL,"
                s = s.strip(",") + ")\n"
                
                try:
                    count = count + 1
                    cur.execute(s)
                except:
                    print("ERROR in :" + s)
                    pass
        else:
            print("No Entry in :" + matnum)
    
    con.commit()
    cur.close();
    con.close();

# Excute below script.
# main(dbname, user, passwd, csvfl)
if __name__ == "__main__":
    # Path to an original xml file object.
    if len(argv) < 4: exit(1)
    main(sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4])


