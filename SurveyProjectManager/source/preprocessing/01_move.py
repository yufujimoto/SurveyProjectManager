#!/usr/bin/python
# -*- coding: utf-8 -*-
from sys import argv
import os, sys, zipfile, shutil
	
def main(indir, outdir, pref=""):
    """
    Move all files included in the origination directory to the destination directory.
    
    @type indir : String
    @type outdir : String
    
    @param indir : A origination directory to parse.
    @param outdir : A destination directory to put. 
    
    @rtype : void
    @return : Nothing.
    """
    
    # Initialyze the variables.
    count = None    # Counting files.
    logfl = None    # Log file for writing current number of files.
    
    # Check whether the logfile is existing or not.
    if os.path.exists(os.path.join(outdir, "log.txt")):
        logfl = open(os.path.join(outdir, "log.txt"), "r")
        for line in logfl:
            entity = line.split(":")
            if entity[0] == "count": count = int(entity[1])
        logfl.close()
    else:
        count = 1
    
    # Set the current count number.
    num = count
    
    # Get image files included in input directory.
    files = getFilesWithExt(indir,".JPG")
    moveToDestination(files, outdir, pref, num)
    
    num = count
    files = getFilesWithExt(indir,".ARW")
    moveToDestination(files, outdir, pref, num)
    
    logfl = open(os.path.join(outdir, "log.txt"), "w")
    logfl.write("count:" + str(num))
    logfl.close()

def moveToDestination(infiles, outdir, pref, num):
    # Move image files to destination.
    for file in sorted(infiles):
        # Get the file name and its extension.
        filename = os.path.basename(file)
        extension = os.path.splitext(file)[1]
        
        # Give a prefix with the parent directory name.
        pref = os.path.basename(os.path.abspath(os.path.join(file, os.pardir)))
        
        # Give a new file name.
        newdir = os.path.join(outdir, extension.strip("."))
        
        # Create the directory if not existing.
        if not os.path.exists(newdir):
            os.mkdir(newdir)
        
        # Geve a new filename.
        newfile = os.path.join(newdir, str(pref) + "_" + str("{0:0>6}".format(num)) + str(extension))
        
        # Move to destination directory
        shutil.copy(file, newfile)
        
        # Increment the count number
        num += 1

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

def getFilesWithoutExt(indir):
    """
    Recursively get a list of files of a given directory without specified extension.
    
    @type	indir	:	String
    @param	indir	:	A given directory to parse.
    
    @rtype		:	List
    @return		:	A string list denoting fullpath of files.
    """
    files = []
    for child in sorted(os.listdir(indir)):
        fullpath = os.path.join(indir, child)
        filename = os.path.splitext(fullpath)[0]
        extension = os.path.splitext(fullpath)[1].lower()
        if os.path.isdir(fullpath):
            files.extend(getFilesWithoutExt(fullpath))
        else:
            files.append(fullpath)
    return files

if __name__ == "__main__":
    # Path to an original xml file object.
    if len(argv) < 3: exit(1)
    main(sys.argv[1], sys.argv[2])


