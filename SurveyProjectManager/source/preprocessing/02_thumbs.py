#!/usr/bin/python
# -*- coding: utf-8 -*-
import sys, os, getopt, psycopg2, argparse, inspect, numpy, zbar, math, csv, uuid
from sys import argv
from PIL import Image, ImageDraw
from PIL.ExifTags import TAGS, GPSTAGS

def main(indir, outdir, basewidth):
    files = getFilesWithExt(indir,".JPG")
    
    for file in files:
        filename = os.path.basename(os.path.splitext(file)[0])
        extension = os.path.splitext(file)[1]
        output = os.path.join(outdir, filename + "_thm" + extension)
        thumbnailImage(file, output, basewidth)


def getFilesWithExt(indir, ext):
    """
    Recursively get a list of files of a given directory with specified extension.
    
    @type	indir	:	String
    @type	ext	:	String
    
    @param	indir	:	A given directory to parse.
    @param	ext	:	Extension for search like as ".jpeg".
    
    @rtype		:	List
    @return		:	A string list denoting fullpath of files.
    """
    files = []
    
    for child in sorted(os.listdir(indir)):
        fullpath = os.path.join(indir, child)
        filename = os.path.splitext(fullpath)[0]
        extension = os.path.splitext(fullpath)[1]
        if os.path.isdir(fullpath):
            files.extend(getFilesWithExt(fullpath, ext))
        elif extension == ext:
            files.append(fullpath)
    return files


def thumbnailImage(imgfile, output, basewidth):
    img = Image.open(imgfile)
    scale = 0
    wsize = 0
    hsize = 0
    
    if img.size[0]>=img.size[1]:
        scale=(float(basewidth)/float(img.size[0]))
        wsize = basewidth
        hsize = int((float(img.size[1])*float(scale)))
    else:
        scale=(float(basewidth)/float(img.size[1]))
        wsize = int((float(img.size[0])*float(scale)))
        hsize = basewidth
    print(output)
    img = img.resize([int(wsize), int(hsize)])
    img.save(output)


if __name__ == "__main__":
    # Path to an original xml file object.
    if len(argv) < 4: exit(1)
    main(sys.argv[1], sys.argv[2], sys.argv[3])
