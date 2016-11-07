#!/usr/bin/python
from sys import argv
import os, sys, inspect, numpy
import zbar, math
from PIL import Image, ImageDraw

class qrobject(object):
  '''
  This class defines the QR code object, which is captured on a image.
  '''
  __slots__ = ("__xArray", "__yArray", "__rotation", "__width", "__height", "__contents")
  
  def __init__(self):
    self.__xArray = []
    self.__yArray = []
    self.__rotation = None
    self.__width = None
    self.__height = None
    self.__contents = []
  
  def __setXArray(self, value): self.__xArray.append(value)
  def __getXArray(self): return self.__xArray
  
  def __setYArray(self, value): self.__yArray.append(value)
  def __getYArray(self): return self.__yArray
  
  def __setRotation(self, value): self.__rotation = value
  def __getRotation(self): return self.__rotation
  
  def __setWidth(self, value): self.__width = value
  def __getWidth(self): return self.__width
  
  def __setLength(self, value): self.__height = value
  def __getLength(self): return self.__height
  
  def __setContetns(self, value): self.__contents.append(value)
  def __getContents(self): return self.__contents
  
  xArray = property(fget=__getXArray, fset=__setXArray)
  yArray = property(fget= __getYArray, fset=__setYArray)
  rotation = property(fget=__getRotation, fset=__setRotation)
  width = property(fget=__getWidth, fset=__setWidth)
  height = property(fget=__getLength, fset=__setLength)
  contents = property(fget=__getContents, fset=__setContetns)

def main(indir):
  prv = "init"
  
  for child in sorted(os.listdir(indir)):
    # Get a full path of the directories.
    fll = os.path.join(indir, child)
    nam = os.path.splitext(fll)[0]
    ext = os.path.splitext(fll)[1].lower()
    
    if os.path.isdir(fll):
      main(fll)
    elif ext == ".JPEG" or ext == ".jpeg" or ext == ".JPG" or ext == ".jpg":
      # Get a list of directories and files.
      if not os.path.exists(os.path.join(indir,"qrinfo.txt")):
        output = open(os.path.join(indir,"qrinfo.txt"),"w")
      else:
        output = open(os.path.join(indir,"qrinfo.txt"),"a")
      
      qr = getQr(fll)
      
      if qr == None:
        print(child+":error in zbar library image!!:")
        output.write(child+":error in zbar library image!!::"+"\n")
        prv = "none"
      else:
        if len(qr) > 0:
          for q in qr:
            print(child+":"+str(q.contents[0])+":"+str(180-q.rotation)+":"+str((q.width+q.height)/2))
            output.write(child+":"+str(q.contents[0])+":"+str(180-q.rotation)+":"+str((q.width+q.height)/2)+"\n")
            prv = str(q.contents[0])
        else:
            if not prv == "none":
              print(child+":"+str(prv)+"::")
              output.write(child+":"+str(prv)+"::"+"\n")
              prv = "none"
            else:
              print(child+":error in previous image!!::")
              output.write(child+":error in previous image!!::"+"\n")
              prv = "none"
      output.close()

def getQr(path):
  try:
    # create a reader
    scanner = zbar.ImageScanner()
    
    # configure the reader
    scanner.parse_config('enable')
    
    # obtain image data
    pil = Image.open(path).convert('L')
    width, height = pil.size
    raw = pil.tostring()
    
    # wrap image data
    image = zbar.Image(width, height, 'Y800', raw)
    
    # scan the image for barcodes
    scanner.scan(image)
    #print "File Name:%s" % path
      
    # extract results
    qrcodes = []
    
    for symbol in image:
      qr = qrobject()
      dst = 0
      crd = []
      
      try:
        qr.xArray=symbol.location[0][0]
        qr.xArray=symbol.location[1][0]
        qr.xArray=symbol.location[2][0]
        qr.xArray=symbol.location[3][0]
        
        qr.yArray=symbol.location[0][1]
        qr.yArray=symbol.location[1][1]
        qr.yArray=symbol.location[2][1]
        qr.yArray=symbol.location[3][1]
        
        qr.contents = symbol.data.strip()
        qr.rotation = (math.atan2(qr.yArray[0]-qr.yArray[3], qr.xArray[0]-qr.xArray[3]))*(180/math.pi)
        qr.width = math.sqrt(math.pow(qr.xArray[3]-qr.xArray[0],2)+math.pow(qr.yArray[3]-qr.yArray[0],2))
        qr.height = math.sqrt(math.pow(qr.xArray[0]-qr.xArray[1],2)+math.pow(qr.yArray[0]-qr.yArray[1],2))
        
        qrcodes.append(qr)
      except:
        pass
    
    # clean up
    del(image)
    return qrcodes
  except ValueError:
    print("Error")
    return None

def centroid(x, y):
  res = []
  n = len(x)-1
  s = 0
  cx = 0
  cy = 0
  
  for i in range(0, n):
    s = s + (x[i] * y[i+1] - x[i+1] * y[i])
  s = s/2
  
  for i in range(0, n):
    cx = cx + (x[i] + x[i+1]) * (x[i] * y[i+1] - x[i+1] * y[i])
    cy = cy + (y[i] + y[i+1]) * (x[i] * y[i+1] - x[i+1] * y[i])
  
  res.append(cx * 1 / (6 * s))
  res.append(cy * 1 / (6 * s))
  
  return res
  
if __name__ == "__main__":
  # Path to an original xml file object.
  if len(argv) < 2: exit(1)
  main(sys.argv[1])

