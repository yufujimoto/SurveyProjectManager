#!/bin/bash -       
#title          :process.sh
#description    :This script will reset the dabase.
#author         :Yu Fujimoto
#date           :20161105
#version        :0.1.0
#usage          :sh process.sh
#==============================================================================

# << 1. Define the directories >>
ttl="project_name"

# Define the root directories
rtd="/root/Project/"

tmp="$rtd""$ttl""/TEMP"
src="$rtd""$ttl""/Source"
prj="$rtd""$ttl""/Project/"
d2d="$prj""DayToDay/"
pht="$prj""DayToDay/Photo/"
con="$prj""Consolidation/"
mat="$con""Materials/"
dbname="db_name"
user="db_user"
passwd="db_pswd"

mkdir "$tmp"
mkdir "$prj"
mkdir "$d2d"
mkdir "$pht"
mkdir "$con"
mkdir "$mat"

# << 1. Move image files >>
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
prf="3046620"
python2 "$src""/01_move.py" "$tmp" "$mat" "$prf"

# << 2. Create thambnail images >>
main=$mat"Main"
thm=$mat"Thumbs"

mkdir "$thm"
python2 "$src""/02_thumbs.py" "$main" "$thm" 600

# << 2. Get QR information >>
thm=$mat"Thumbs"
python2 "$src""/03_readQr.py" "$thm"

con_1="$con""TeradaStudio/"
csvfl="$con_1""imagelists.csv"
python2 "$src""/04_importimages.py" "$dbname" "$user" "$passwd" "$csvfl"
