#!/bin/bash -       
#title          :process.sh
#description    :This script will reset the dabase.
#author         :Yu Fujimoto
#date           :20161105
#version        :0.1.0
#usage          :sh process.sh
#==============================================================================

# << 1. Move image files >>
srcdir="/var/www/html/SurveyProjectManager/SurveyProjectManager/source/preprocessing/"
indir="/home/yufujimoto/Documents/Projects/Danjiri/2016/Project/DayToDay/Photo/"
outdir="/home/yufujimoto/Documents/Projects/Danjiri/2016/Project/Image/Consolidation/Materials/"

# Input directory structure is below:
# ==============================================
# $indir
# |---[serial number of the digitizing device 1]
# |   +--- [aquired image file 1]
# |   +--- [aquired image file 2]
# |   +--- [aquired image file 3]
# |   .
# |   +--- [aquired image file n]
# |
# +---[serial number of the digitizing device 2]
# |   +--- [aquired image file 1]
# |   +--- [aquired image file 2]
# |   +--- [aquired image file 3]
# |   .
# |   +--- [aquired image file n]
# |
# +---[serial number of the digitizing device 3]
#     +--- [aquired image file 1]
#     +--- [aquired image file 2]
#     +--- [aquired image file 3]
#     .
#     .
#     .
#     +--- [aquired image file n]
# ==============================================

python $srcdir"01_move.py" $indir $outdir

# << 2. Get QR information >>
python $srcdir"02_readQr.py" $outdir
