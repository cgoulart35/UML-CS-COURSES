#retrieved code from https://www.gioexperience.com/google-home-hack-send-voice-programmaticaly-with-python/
#by Giovanni, 11/27/2018

import sys
import pychromecast
import os
import os.path
from gtts import gTTS
import time
import hashlib

ip=sys.argv[1];
say=sys.argv[2];

import socket
s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
s.connect(("8.8.8.8", 80))
local_ip=s.getsockname()[0]
s.close()

fname=hashlib.md5(say.encode()).hexdigest()+".mp3"; #create md5 filename for caching
if say == 'dance':
	fname = 'dance.mp3'
elif say == 'thatsHot':
	fname = 'thatsHot.mp3'

castdevice = pychromecast.Chromecast(ip)
castdevice.wait()
vol_prec=castdevice.status.volume_level

if not os.path.isfile(fname):
   tts = gTTS(say,lang='en')
   tts.save(fname)

mc = castdevice.media_controller
mc.play_media("http://"+local_ip+":8000/"+fname, "audio/mp3")

mc.block_until_active()

mc.pause()

time.sleep(1)
castdevice.set_volume(vol_prec)
time.sleep(0.2)

mc.play()

while not mc.status.player_is_idle:
   time.sleep(0.5)

mc.stop()

castdevice.quit_app()