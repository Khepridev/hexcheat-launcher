# Generated with Gui Editor by KaMeR1337 ; www.metin2mod.tk
# -*- coding: utf-8 -*-
import dbg
import chr
try:
    import playerm2g2 as player
    chr.GetPixelPosition = player.GetMainCharacterPosition #na globalu zastapic funkcje - chr.MoveToDestPosition(myvid, x, y) - ruch do pozycji myvid do z i y pozycja moba
                                                            #na globalu zastapic x, y, z = chr.GetPixelPosition(vid)- x,y,z to pozycja dowolnego vid
except:
    import player
try:
    import chatm2g as chat
except:
    import chat
try:
    import m2netm2g as net
except:
    import net    
import app
import time
import os
import wndMgr
import locale
import interfacemodule
import background
import item
import textTail
import shop
import mouseModule
import grp
import uiToolTip
import nonplayer
import systemSetting
from uitooltip import ItemToolTip
import snd
import skill
try:
    import jebac_mh as ui
except:
    import ui
pelerin_sayac=0
pelerin_sureleri=['1','3','5','7','9','11']
sabit_koord=[]
pelerinlist=['Normal','Archer','Mega Pelerin','Mega Archer']
metin_secenek_list=['Yuru','Isinlan']
sayac=0
portlar=27102,27105
koord1=[]
idler=[7140,50311,50312,50313,10,11,12,13,14,15,20,21,22,23,24,25,30,31,32,33,34,35,40,41,42,43,44,45,50,51,52,53,54,55,60,61,62,63,64,65,70,71,72,73,74,75,80,81,82,83,84,85,90,91,92,93,94,95,100,101,102,103,104,105,110,111,112,113,114,115,120,121,122,123,124,125,130,131,132,133,134,135,150,151,152,153,154,155,160,161,162,163,164,165,170,171,172,173,174,175,190,191,192,193,194,195,240,241,242,243,244,245,250,251,252,253,254,255,260,261,262,263,264,265,1000,1001,1002,1003,1004,1005,1010,1011,1012,1013,1014,1015,1020,1021,1022,1023,1024,1025,1030,1031,1032,1033,1034,1035,1040,1041,1042,1043,1044,1045,1050,1051,1052,1053,1054,1055,1060,1061,1062,1063,1064,1065,1070,1071,1072,1073,1074,1075,1080,1081,1082,1083,1084,1085,1090,1091,1092,1093,1094,1095,1100,1101,1102,1103,1104,1105,1110,1111,1112,1113,1114,1115,1120,1121,1122,1123,1124,1125,1130,1131,1132,1133,1134,1135,1140,1141,1142,1143,1144,1145,1150,1151,1152,1153,1154,1155,1160,1161,1162,1163,1164,1165,1170,1171,1172,1173,1174,1175,2000,2001,2002,2003,2004,2005,2010,2011,2012,2013,2014,2015,2020,2021,2022,2023,2024,2025,2030,2031,2032,2033,2034,2035,2040,2041,2042,2043,2044,2045,2050,2051,2052,2053,2054,2055,2060,2061,2062,2063,2064,2065,2070,2071,2072,2073,2074,2075,2080,2081,2082,2083,2084,2085,2090,2091,2092,2093,2094,2095,2100,2101,2102,2103,2104,2105,2110,2111,2112,2113,2114,2115,2120,2121,2122,2123,2124,2125,2130,2131,2132,2133,2134,2135,2150,2151,2152,2153,2154,2155,2190,2191,2192,2193,2194,2195,3000,3001,3002,3003,3004,3005,3010,3011,3012,3013,3014,3015,3020,3021,3022,3023,3024,3025,3030,3031,3032,3033,3034,3035,3040,3041,3042,3043,3044,3045,3050,3051,3052,3053,3054,3055,3060,3061,3062,3063,3064,3065,3070,3071,3072,3073,3074,3075,3080,3081,3082,3083,3084,3085,3090,3091,3092,3093,3094,3095,3100,3101,3102,3103,3104,3105,3110,3111,3112,3113,3114,3115,3120,3121,3122,3123,3124,3125,3130,3131,3132,3133,3140,3141,3142,3143,3144,3145,4000,4001,4002,4003,4004,4005,4010,4011,4012,4013,4014,4015,4020,4021,4022,4023,4024,4025,4030,4031,4032,4033,4034,4035,5000,5001,5002,5003,5004,5005,5010,5011,5012,5013,5014,5015,5020,5021,5022,5023,5024,5025,5030,5031,5032,5033,5034,5035,5040,5041,5042,5043,5044,5045,5050,5051,5052,5053,5054,5055,5060,5061,5062,5063,5064,5065,5070,5071,5072,5073,5074,5075,5080,5081,5082,5083,5084,5085,5090,5091,5092,5093,5094,5095,5100,5101,5102,5103,5104,5110,5111,5112,5113,5114,5115,7000,7001,7002,7003,7004,7005,7010,7011,7012,7013,7014,7015,7020,7021,7022,7023,7024,7025,7030,7031,7032,7033,7034,7035,7040,7041,7042,7043,7044,7045,7050,7051,7052,7053,7054,7055,7060,7061,7062,7063,7064,7065,7070,7071,7072,7073,7074,7075,7080,7081,7082,7083,7084,7085,7090,7091,7092,7093,7094,7095,7100,7101,7102,7103,7104,7105,7110,7111,7112,7113,7114,7115,7120,7121,7122,7123,7124,7125,7130,7131,7132,7133,7134,7135,7150,7151,7152,7153,7154,7155,7160,7161,7162,7163,7164,7165,7170,7171,7172,7173,7174,7175,9501,9502,9503,9504,9505,9506,9507,9508,9509,11200,11201,11202,11203,11204,11205,11210,11211,11212,11213,11214,11215,11220,11221,11222,11223,11224,11225,11230,11231,11232,11233,11234,11235,11240,11241,11242,11243,11244,11245,11250,11251,11252,11253,11254,11255,11260,11261,11262,11263,11264,11265,11270,11271,11272,11273,11274,11275,11280,11281,11282,11283,11284,11285,11400,11401,11402,11403,11404,11405,11410,11411,11412,11413,11414,11415,11420,11421,11422,11423,11424,11425,11430,11431,11432,11433,11434,11435,11440,11441,11442,11443,11444,11445,11450,11451,11452,11453,11454,11455,11460,11461,11462,11463,11464,11465,11470,11471,11472,11473,11474,11475,11480,11481,11482,11483,11484,11485,11600,11601,11602,11603,11604,11605,11610,11611,11612,11613,11614,11615,11620,11621,11622,11623,11624,11625,11630,11631,11632,11633,11634,11635,11640,11641,11642,11643,11644,11645,11650,11651,11652,11653,11654,11655,11660,11661,11662,11663,11664,11665,11670,11671,11672,11673,11674,11675,11680,11681,11682,11683,11684,11685,11800,11801,11802,11803,11804,11805,11810,11811,11812,11813,11814,11815,11820,11821,11822,11823,11824,11825,11830,11831,11832,11833,11834,11835,11840,11841,11842,11843,11844,11845,11850,11851,11852,11853,11854,11855,11860,11861,11862,11863,11864,11865,11870,11871,11872,11873,11874,11875,11880,11881,11882,11883,11884,11885,12200,12201,12202,12203,12204,12205,12220,12221,12222,12223,12224,12225,12240,12241,12242,12243,12244,12340,12341,12342,12343,12344,12345,12360,12361,12362,12363,12364,12365,12380,12381,12382,12383,12384,12385,12480,12481,12482,12483,12484,12485,12500,12501,12502,12503,12504,12505,12520,12521,12522,12523,12524,12525,12620,12621,12622,12623,12624,12625,12640,12641,12642,12643,12644,12645,12660,12661,12662,12663,12664,12665,13000,13001,13002,13003,13004,13020,13021,13022,13023,13024,13040,13041,13042,13043,13044,13190,13191,13192,13193,13194,13200,13201,13202,13203,13204,17041,17042,17043,17044,17045,17080,17081,17082,17083,17084,17085,17120,17121,17122,17123,17124,17125,17140,17141,17142,17143,17144,17145,17160,17161,17162,17163,17164,17165,17180,17181,17182,17183,17184,17185,17200,17201,17202,17203,17204,17205]
degisken=0
maden=['Grena Damari','Elmas Damari','Ruh Kristali','Fosillesmis Agac Damari','Bakir Damari','Gumus Damari','Altin Damari','Zumrut Damari','Abanoz Damari','Inci Damari','Beyaz Altin Damari','Kristal Damari','Ametist Damari','Cennetin Gozu Damari']
modlist=['Tek El','Cift El','Yay','Bicak','Yelpaze','Can','Atta Tek El','Atta Cift El','Atta Yay','Atta Bicak','Atta Can','Olta','Yumruk']
class BaslatPaneli(ui.Bar):
    def __init__(self, main_dialog):
        ui.Bar.__init__(self)
        self.main_dialog = main_dialog
        self.SetPosition(0, wndMgr.GetScreenHeight() // 2 - 20)
        self.SetSize(100, 40)
        self.AddFlag("float")
        self.SetColor(0x00000000)  # Tam şeffaf

        self.baslat_butonu = ui.Button()
        self.baslat_butonu.SetParent(self)
        self.baslat_butonu.SetPosition(0, 0)
        self.baslat_butonu.SetUpVisual("d:/ymir work/ui/public/large_button_01.sub")
        self.baslat_butonu.SetOverVisual("d:/ymir work/ui/public/large_button_02.sub")
        self.baslat_butonu.SetDownVisual("d:/ymir work/ui/public/large_button_03.sub")
        self.baslat_butonu.SetText("Göster")
        self.baslat_butonu.SetEvent(ui.__mem_func__(self.OnClick))
        self.baslat_butonu.Show()

        self.Show()

    def OnClick(self):
        
        self.main_dialog.Show()


class TimerManager:
    def __init__(self):
        self.timers = {}  # Çalışan fonksiyonları saklıyoruz
        self.saved_timers = {}  # Zamanlayıcıları yeniden başlatmak için saklıyoruz

    def add_timer(self, key, interval, function):
        """ Yeni bir zamanlı işlem ekler """
        self.timers[key] = {
            "interval": interval,
            "last_time": app.GetTime(),
            "function": function
        }
        self.saved_timers[key] = (interval, function)  # Zamanlayıcıyı sakla

    def remove_timer(self, key):
        """ Bir işlemi zamanlayıcıdan kaldırır """
        if key in self.timers:
            del self.timers[key]
        if key in self.saved_timers:
            del self.saved_timers[key]

    def update(self):
        """ Tüm zamanlı işlemleri günceller """
        current_time = app.GetTime()
        for key, timer in self.timers.items():
            if current_time - timer["last_time"] >= timer["interval"]:
                timer["function"]()
                timer["last_time"] = current_time

    def restart_timers(self):
        """ Tüm zamanlayıcıları yeniden başlatır """
        for key, (interval, function) in self.saved_timers.items():
            self.add_timer(key, interval, function)

    def get_timer_remaining_time(self, key):
        """ Belirli bir zamanlayıcının kalan süresini döndürür """
        if key in self.timers:
            current_time = app.GetTime()
            timer = self.timers[key]
            elapsed_time = current_time - timer["last_time"]
            remaining_time = timer["interval"] - elapsed_time
            return max(0, remaining_time)
        return None


# **Timer yöneticisini oluştur**
timer_manager = TimerManager()
class MetinBot(ui.Window):
    def __init__(self):
        ui.Window.__init__(self)
        self.route = []
        self.file_path = "metin_koord.cfg"
        self.file_path2 = "metin_koord2.cfg"
        self.file_path3 = "metin_koord3.cfg"
        self.current_target_index = 0
        self.follow_route_timer_key = "follow_route"
        self.is_moving = False
        self.reverse_route = False
        self.last_position = None
        self.check_movement_timer_key = "check_movement"
        self.skill_timer_key = "skill_timer"
        self.skill_check_timer_key = "skill_check_timer"
        self.skill_step = 0

    def check_dead(self):
        """Karakterin ölü olup olmadığını kontrol eder"""
        if player.GetStatus(player.HP) == 0:
            self.restart_character()

    def restart_character(self):
        """Karakteri yeniden başlatır"""
        net.SendChatPacket("/restart")
        chat.AppendChat(2, "Karakter yeniden başlatıldı.")


    def activate_skills(self):
        self.skill_step = 0
        timer_manager.add_timer(self.skill_timer_key, 0.7, self.use_skills)
        
    def use_skills(self):
        karakter_turu=player.GetJob()
        if karakter_turu==0:
            dialog.sure_sayaci.SetText("Kullanılacak Skill : Hava Kılıcı")
            if player.IsMountingHorse():

                try:

                    # Hava Kılıcı skili için bekleme süresi ve geçen süreyi al
                    cooldown, elapsed_time = player.GetSkillCoolTime(4)
                    # Eğer geçen süre, bekleme süresinden 5 saniye daha büyükse skili aç
                    if elapsed_time >= cooldown + 5:
                        # Eğer karakter attaysa, attan in
                        if player.IsMountingHorse():
                            net.SendChatPacket("/ride") 

                        # Hava Kılıcı yeteneğini aç
                        player.ClickSkillSlot(4)
                
                        # 1 saniye sonra ata bin
                        timer_manager.add_timer("mount_horse", 1.0, self.mount_horse)
                

                except Exception as e:
                    chat.AppendChat(3,"Yetenekleri aktifleştirme hatası: "+{str(e)})
            elif not player.IsMountingHorse():
                cooldown, elapsed_time = player.GetSkillCoolTime(4)
                if elapsed_time >= cooldown + 5:
                     player.ClickSkillSlot(4)


        elif karakter_turu==2:
            dialog.sure_sayaci.SetText("Kullanılacak Skill : Büyülü Keskinlik")
            if player.IsMountingHorse():
                try:
                
                    # Eğer geçen süre, bekleme süresinden 5 saniye daha büyükse skili aç
                    if elapsed_time >= cooldown + 100:
                        # Eğer karakter attaysa, attan in
                        if player.IsMountingHorse():
                            net.SendChatPacket("/ride") 

                        #Büyülü Keskinlik yeteneğini aç
                        player.ClickSkillSlot(3)
                        player.ClickSkillSlot(3)
                
                        # 1 saniye sonra ata bin
                        timer_manager.add_timer("mount_horse", 1.0, self.mount_horse)
                        timer_manager.remove_timer(self.skill_timer_key)
                        timer_manager.add_timer(self.skill_timer_key,100.0,self.use_skills)
            

                except Exception as e:
                    chat.AppendChat(3,"Yetenekleri aktifleştirme hatası: "+{str(e)})
            elif not player.IsMountingHorse():
                if player.IsSkillActive(3):
                    player.ClickSkillSlot(3)
                    player.ClickSkillSlot(3)
                    timer_manager.remove_timer(self.skill_timer_key)
                    timer_manager.add_timer(self.skill_timer_key,100.0,self.use_skills)
                elif not player.IsSkillActive(3):
                    player.ClickSkillSlot(3)
                    timer_manager.remove_timer(self.skill_timer_key)
                    timer_manager.add_timer(self.skill_timer_key,100.0,self.use_skills)
            

    def mount_horse(self):
        """Karakteri ata bindirir."""
        if not player.IsMountingHorse():
            net.SendChatPacket("/ride")
            


    def disable_skills(self):
        timer_manager.remove_timer(self.skill_timer_key)
        timer_manager.remove_timer("mount_horse")
        chat.AppendChat(2, "Yetenek kullanımı devre dışı bırakıldı.")



    def SaveCurrentPos(self):
        try:
            x, y, z = player.GetMainCharacterPosition()
            pos = (int(x), int(y))
            f = open(self.file_path, "a")
            f.write("%d,%d\n" % (pos[0], pos[1]))
            f.close()
            chat.AppendChat(2, "Koordinat kaydedildi: %d,%d" % (pos[0], pos[1]))
            self.route.append(pos)
        except Exception as e:
            chat.AppendChat(3, "Kaydetme hatası: %s" % str(e))

    def load_coordinates(self):
        self.route=[]
        try:
            f = open("metin_koord.cfg", "r")
            for line in f:
                line = line.strip()
                if line:  # boş satırları atla
                    x, y = map(int, line.split(","))
                    self.route.append((x, y))
            f.close()
            chat.AppendChat(2, "Toplam %d nokta yüklendi" % len(self.route))
        except Exception as e:
            chat.AppendChat(3, "Yükleme hatası: %s" % str(e))
    def SaveCurrentPos2(self):
        try:
            x, y, z = player.GetMainCharacterPosition()
            pos = (int(x), int(y))
            f = open(self.file_path2, "a")
            f.write("%d,%d\n" % (pos[0], pos[1]))
            f.close()
            chat.AppendChat(2, "Koordinat kaydedildi: %d,%d" % (pos[0], pos[1]))
            self.route.append(pos)
        except Exception as e:
            chat.AppendChat(3, "Kaydetme hatası: %s" % str(e))

    def load_coordinates2(self):
        self.route=[]
        try:
            f = open("metin_koord2.cfg", "r")
            for line in f:
                line = line.strip()
                if line:  # boş satırları atla
                    x, y = map(int, line.split(","))
                    self.route.append((x, y))
            f.close()
            chat.AppendChat(2, "Toplam %d nokta yüklendi" % len(self.route))
        except Exception as e:
            chat.AppendChat(3, "Yükleme hatası: %s" % str(e))


    def SaveCurrentPos3(self):
        try:
            x, y, z = player.GetMainCharacterPosition()
            pos = (int(x), int(y))
            f = open(self.file_path3, "a")
            f.write("%d,%d\n" % (pos[0], pos[1]))
            f.close()
            chat.AppendChat(2, "Koordinat kaydedildi: %d,%d" % (pos[0], pos[1]))
            self.route.append(pos)
        except Exception as e:
            chat.AppendChat(3, "Kaydetme hatası: %s" % str(e))

    def load_coordinates3(self):
        self.route=[]
        try:
            f = open("metin_koord3.cfg", "r")
            for line in f:
                line = line.strip()
                if line:  # boş satırları atla
                    x, y = map(int, line.split(","))
                    self.route.append((x, y))
            f.close()
            chat.AppendChat(2, "Toplam %d nokta yüklendi" % len(self.route))
        except Exception as e:
            chat.AppendChat(3, "Yükleme hatası: %s" % str(e))

    def ClearRoute(self):
        try:
            if os.path.exists(self.file_path):
                os.remove(self.file_path)
            self.route = []
            chat.AppendChat(2, "Rota temizlendi!")
        except Exception as e:
            chat.AppendChat(3, "Temizleme hatası: %s" % str(e))

    def follow_route(self):
        """ Kaydedilen rotaya göre hareket eder ve metin taşlarını kontrol eder """
        if not self.route:
            chat.AppendChat(7, "Rota boş!")
            return

        if self.is_moving:
            # Hedefe ulaşılıp ulaşılmadığını kontrol et
            p_x, p_y, p_z = player.GetMainCharacterPosition()
            target_x, target_y = self.route[self.current_target_index]
            if abs(p_x - target_x) < 100 and abs(p_y - target_y) < 100:
                self.is_moving = False
                if self.reverse_route:
                    self.current_target_index -= 1
                    if self.current_target_index < 0:
                        self.current_target_index = 0
                        self.reverse_route = False
                else:
                    self.current_target_index += 1
                    if self.current_target_index >= len(self.route):
                        self.current_target_index = len(self.route) - 1
                        self.reverse_route = True
        else:
            # Yeni hedefe git
            p = player.GetMainCharacterIndex()
            target_x, target_y = self.route[self.current_target_index]
            #chat.AppendChat(2, "Hedefe gidiliyor: X: %d, Y: %d" % (target_x, target_y))
            chr.MoveToDestPosition(p, target_x, target_y)
            self.is_moving = True
            self.check_for_metin()
    def rota_reset(self):
        self.current_target_index = 0
        self.reverse_route = False
    def skil_deneme(self):
        #net.SendItemUsePacket(0)
        aydi = player.GetItemIndex(0)
        chat.AppendChat(2,str(aydi))
        chr.PushOnceMotion(chr.MOTION_COMBO_ATTACK_1)
        
        #chat.AppendChat(2, "Item ID: %d" % aydi)
    def start_follow_route(self):
        """ follow_route metodunu belirli aralıklarla çağırır """
        self.is_moving = False  # Hareketi sıfırla
        self.last_position = player.GetMainCharacterPosition()
        timer_manager.add_timer(self.follow_route_timer_key, 0.1, self.follow_route)
        timer_manager.add_timer(self.check_movement_timer_key, 0.3, self.check_movement)

    def stop_follow_route(self):
        """ follow_route metodunu çağıran zamanlayıcıyı durdurur """
        timer_manager.remove_timer(self.follow_route_timer_key)
        timer_manager.remove_timer(self.check_movement_timer_key)
        player.SetAttackKeyState(FALSE)
        self.is_moving = False  # Hareketi sıfırla

    def check_movement(self):
        """ Karakterin hareket edip etmediğini kontrol eder """
        current_position = player.GetMainCharacterPosition()
        if self.last_position == current_position:
            self.is_moving = False
        self.last_position = current_position

    def check_for_metin(self): 
        global degisken,sayac
        player.SetAttackKeyState(FALSE)

        o = player.GetMainCharacterIndex()
        degisken += 5  

        closest_metin = None  # closest_metin değişkenini başta None olarak tanımlayın
        closest_distance = float('inf')  # Başlangıçta en uzak mesafe

        # **Düşmanları Manuel Tara**
        if player.SetAttackKeyState==TRUE:
            chat.AppendChat(2, "Saldırı başladı.")
           
        else:
            for i in xrange(o - 500000, o + 50000):  # ID aralığını geniş tut
                a = chr.GetInstanceType(i)
                if a == 2:
                    mesafe = player.GetCharacterDistance(i)
                    if 0 < mesafe < 1000:  # 5.000 menzil içinde mi?
                        if mesafe < closest_distance:  # En yakını kontrol et
                            closest_distance = mesafe
                            closest_metin = i

            # **Eğer en yakın metin bulunduysa saldır**
            if closest_metin:
                self.attacking_metin = closest_metin
                player.SetTarget(closest_metin)  # Hedefi belirle
                x, y, z = chr.GetPixelPosition(closest_metin)
                chr.MoveToDestPosition(o, int(x), int(y))  # Metne git

                if closest_distance < 200:  # Yaklaşınca saldır
                    player.SetTarget(closest_metin)
                    chr.LookAt(closest_metin)
                    chr.SelectInstance(closest_metin)
                    player.SetAttackKeyState(TRUE)
                    self.is_moving = False  # Hareketi durdur


            # Eğer metin öldüyse saldırıyı durdur
            if self.attacking_metin and not chr.HasInstance(self.attacking_metin):
                self.attacking_metin = None
                player.SetAttackKeyState(FALSE)
                chat.AppendChat(2, "Metin öldü, saldırı durduruldu.")
                sayac=sayac+1
                dialog.metin_sayac.SetText(str(sayac))
                pass
            
    def start_bot(self):
        """ Sürekli olarak rotayı takip et ve metinleri kontrol et """
        self.follow_route()
        timer_manager.add_timer("bot", 1.0, self.start_bot)


class Dialog1(ui.BoardWithTitleBar):
    def __init__(self):
        ui.BoardWithTitleBar.__init__(self)
        self.metin_bot = MetinBot()  # MetinBot örneğini oluşturun
        self.buffbot = BuffBot()  # BuffBot örneğini oluşturun
        self.BuildWindow()
        self.start_check_connection()
        self.start_check_dead()
        self.update_timer_display()  # Timer display güncellemesini başlat

    def __del__(self):
        ui.BoardWithTitleBar.__del__(self)

    def BuildWindow(self):
        self.SetSize(348, 450)
        screen_width = wndMgr.GetScreenWidth()
        screen_height = wndMgr.GetScreenHeight()
        self.SetPosition(110, (screen_height // 2) - (450 // 2))  # X: 20, Y: Ekranın ortası - pencere yüksekliğinin yarısı
        self.AddFlag('movable')
        self.AddFlag('float')
        self.SetTitleName('Hamurci Hack 1.0 :D')
        self.SetCloseEvent(self.Close)
        self.comp = Component()




        self.koord_ekle = ui.Button()
        self.koord_ekle.SetParent(self)
        self.koord_ekle.SetPosition(10, 231)
        self.koord_ekle.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.koord_ekle.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.koord_ekle.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.koord_ekle.SetEvent(lambda:self.metin_bot.load_coordinates())
        self.koord_ekle.SetText('Rota 1 Yükle')
        self.koord_ekle.SetToolTipText('|cFF00FF001. Rota Dosyasını Yükler|r')
        self.koord_ekle.Show()

        self.kaydet = ui.Button()
        self.kaydet.SetParent(self)
        self.kaydet.SetPosition(30, 260)
        self.kaydet.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.kaydet.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.kaydet.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.kaydet.SetEvent(lambda:self.metin_bot.SaveCurrentPos())
        self.kaydet.SetText('Koord Kaydet')
        self.kaydet.SetToolTipText('|cFF00FF001. Rotayı Kaydet|r')
        self.kaydet.Show()

        self.rota_reset = ui.Button()
        self.rota_reset.SetParent(self)
        self.rota_reset.SetPosition(33, 292)
        self.rota_reset.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.rota_reset.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.rota_reset.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.rota_reset.SetEvent(lambda:self.metin_bot.rota_reset())
        self.rota_reset.SetText('Reset')
        self.rota_reset.SetToolTipText('|cFF00FF00Rotayı Baştan Başlatır|r')

        self.rota_reset.Show()


        self.rota_kizil2 = self.comp.Button(self, 'Rota 2 Yükle', '', 110, 231, lambda:self.metin_bot.load_coordinates2(), 'd:/ymir work/ui/public/large_button_01.sub', 'd:/ymir work/ui/public/large_button_02.sub', 'd:/ymir work/ui/public/large_button_03.sub')
        self.rota_kizil2.SetToolTipText('|cFF00FF002. Rota Dosyasını Yükler|r')
     
        self.kaydet2 = ui.Button()
        self.kaydet2.SetParent(self)
        self.kaydet2.SetPosition(138, 260)
        self.kaydet2.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.kaydet2.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.kaydet2.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.kaydet2.SetEvent(lambda:self.metin_bot.SaveCurrentPos2())
        self.kaydet2.SetText('Koord Kaydet')
        self.kaydet2.SetToolTipText('|cFF00FF002. Rotayı Kaydet|r')
        self.kaydet2.Show()

        self.rota_hayalet = self.comp.Button(self, 'Rota 3 Yükle', '', 212, 231, lambda:self.metin_bot.load_coordinates3(), 'd:/ymir work/ui/public/large_button_01.sub', 'd:/ymir work/ui/public/large_button_02.sub', 'd:/ymir work/ui/public/large_button_03.sub')
        
        self.rota_hayalet.SetToolTipText('|cFF00FF003. Rota Dosyasını Yükler|r')

        self.kaydet3 = ui.Button()
        self.kaydet3.SetParent(self)
        self.kaydet3.SetPosition(231, 260)
        self.kaydet3.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.kaydet3.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.kaydet3.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.kaydet3.SetEvent(lambda:self.metin_bot.SaveCurrentPos3())
        self.kaydet3.SetText('Koord Kaydet')
        self.kaydet3.Show()
        self.kaydet3.SetToolTipText('|cFF00FF003. Rotayı Kaydet|r')


        self.demirci = ui.Button()
        self.demirci.SetParent(self)
        self.demirci.SetPosition(297, 36)
        self.demirci.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.demirci.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.demirci.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.demirci.SetEvent(lambda: self.normal_demirci())
        self.demirci.SetText('Normal')
        self.demirci.SetToolTipText('|cFF00FF00Normal Demircide + Bas|r')
        self.demirci.Show()

        self.xkoord = self.comp.TextLine(self, 'X:              Y:', 154, 41, self.comp.RGB(0, 255, 650))
        self.metin_koord, self.metin_koord_value = self.comp.EditLine(self, '', 165, 39, 35, 17, 5)
        self.metin_koord2, self.metin_koord_value2 = self.comp.EditLine(self, '', 215, 39, 35, 17, 5)
        
        
        self.komut_button = ui.Button()
        self.komut_button.SetParent(self)
        self.komut_button.SetPosition(252, 37)
        self.komut_button.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.komut_button.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.komut_button.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.komut_button.SetEvent(lambda: self.teleport())
        self.komut_button.SetText('Teleport')
        self.komut_button.SetToolTipText('|cFF00FF00Yazılan Koordinatlara Işınlan \n Cadı:1412,1182|r')
        self.komut_button.Show()

        self.kule = ui.Button()
        self.kule.SetParent(self)
        self.kule.SetPosition(297, 60)
        self.kule.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.kule.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.kule.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.kule.SetEvent(lambda:self.kule_demircisi())
        self.kule.SetText('Kule')
        self.kule.SetToolTipText('|cFF00FF00Kule Demircisinde + Bas|r')
        self.kule.Show()



        self.oto_skill = ui.ToggleButton()
        self.oto_skill.SetParent(self)
        self.oto_skill.SetPosition(10, 351)
        self.oto_skill.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.oto_skill.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.oto_skill.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.oto_skill.SetToggleUpEvent(lambda: self.metin_bot.disable_skills())
        self.oto_skill.SetToggleDownEvent(lambda:self.metin_bot.activate_skills())
        self.oto_skill.SetText('Oto Skill')
        self.oto_skill.SetToolTipText('|cFF00FF00Hava Kılıcı/Büyülü Silah Skilini Aç|r')
        self.oto_skill.Show()

       # self.skill_sure, self.skill_sure_value = self.comp.EditLine(self, '', 110, 353, 25, 17,3)
        
        self.modlar = self.comp.ComboBox(self, 'Vurus Modu Sec', 187, 377, 80)
        for index, item in enumerate(modlist):
            self.modlar.InsertItem(index, str(item))
     


        self.otometin_buton = ui.ToggleButton()
        self.otometin_buton.SetParent(self)
        self.otometin_buton.SetPosition(110, 290)
        self.otometin_buton.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.otometin_buton.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.otometin_buton.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.otometin_buton.SetToggleUpEvent(lambda: self.metin_bot.stop_follow_route())
        self.otometin_buton.SetToggleDownEvent(lambda: self.metin_bot.start_follow_route())
        self.otometin_buton.SetText('Rotada ilerle')
        self.otometin_buton.SetToolTipText('|cFF00FF00Seçilen Rotada İlerler ve Metinlere Vurur|r')
        self.otometin_buton.Show()

        self.kombo = ui.ToggleButton()
        self.kombo.SetParent(self)
        self.kombo.SetPosition(275, 356)
        self.kombo.SetUpVisual('d:/ymir work/ui/public/middle_button_01.sub')
        self.kombo.SetOverVisual('d:/ymir work/ui/public/middle_button_02.sub')
        self.kombo.SetDownVisual('d:/ymir work/ui/public/middle_button_03.sub')
        self.kombo.SetToggleDownEvent(self.kombo_ac)
        self.kombo.SetToggleUpEvent(self.kombo_kapa)
        self.kombo.SetText('Kombo')
        self.kombo.SetToolTipText('|cFF00FF00Vuruş Kombosu|r')
        self.kombo.Show()
        self.eldiven = ui.ToggleButton()
        self.eldiven.SetParent(self)
        self.eldiven.SetPosition(275, 336)
        self.eldiven.SetUpVisual('d:/ymir work/ui/public/middle_button_01.sub')
        self.eldiven.SetOverVisual('d:/ymir work/ui/public/middle_button_02.sub')
        self.eldiven.SetDownVisual('d:/ymir work/ui/public/middle_button_03.sub')
        self.eldiven.SetToggleDownEvent(self.eldiven_ac)
        self.eldiven.SetToggleUpEvent(self.eldiven_kapa)
        self.eldiven.SetText('Hırsız Eldiveni')
        self.eldiven.SetToolTipText('|cFF00FF00Hırsız Eldivenini Stackli ise Ayırın|r')
        self.eldiven.Show()


        self.vurus = ui.ToggleButton()
        self.vurus.SetParent(self)
        self.vurus.SetPosition(275, 376)
        self.vurus.SetUpVisual('d:/ymir work/ui/public/middle_button_01.sub')
        self.vurus.SetOverVisual('d:/ymir work/ui/public/middle_button_02.sub')
        self.vurus.SetDownVisual('d:/ymir work/ui/public/middle_button_03.sub')
        self.vurus.SetToggleDownEvent(self.vurus_timer)
        self.vurus.SetToggleUpEvent(self.vurus_kapa)
        self.vurus.SetText('Vuruş Modu')
        self.vurus.SetToolTipText('|cFF00FF00Vuruş Modunu Ayarla|r')
        self.vurus.Show()

        self.oto_sandik = ui.ToggleButton()
        self.oto_sandik.SetParent(self)
        self.oto_sandik.SetPosition(275, 396)
        self.oto_sandik.SetUpVisual('d:/ymir work/ui/public/middle_button_01.sub')
        self.oto_sandik.SetOverVisual('d:/ymir work/ui/public/middle_button_02.sub')
        self.oto_sandik.SetDownVisual('d:/ymir work/ui/public/middle_button_03.sub')
        self.oto_sandik.SetToggleUpEvent(self.otosandik_kapa)
        self.oto_sandik.SetToggleDownEvent(self.otosandik_ac)
        self.oto_sandik.SetText('Oto Sandık')
        self.oto_sandik.SetToolTipText('|cFF00FF00Gümüş ve Altın Sandıkları Otomatik Aç|r')
        self.oto_sandik.Show()

        self.otometin_buton2 = ui.ToggleButton()
        self.otometin_buton2.SetParent(self)
        self.otometin_buton2.SetPosition(10, 176)
        self.otometin_buton2.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.otometin_buton2.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.otometin_buton2.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.otometin_buton2.SetToggleUpEvent(self.stop_metinbot)
        self.otometin_buton2.SetToggleDownEvent(self.start_metinbot)
        self.otometin_buton2.SetText('Metinlere Saldır')
        self.otometin_buton2.Show()

        self.infotext=self.comp.TextLine(self,'Rota kısmında kendinize el ile bir rota çizip o rotada otomatik metin ',10,207,self.comp.RGB(226,135,67))
        self.infotext2=self.comp.TextLine(self,'farmı yapabilirsiniz ',10,217,self.comp.RGB(226,135,67))
        
        self.metin_sayac2 = self.comp.TextLine(self, 'Kesilen Metin Sayacı : ', 220, 295, self.comp.RGB(0, 255, 650))
        self.metin_sayac = self.comp.TextLine(self, '0', 320, 295, self.comp.RGB(0, 255, 650))
        self.sure_sayaci = self.comp.TextLine(self, 'Kullanılacak Skill : ', 10, 373, self.comp.RGB(0, 255, 650))


        self.maden_buton = ui.ToggleButton()
        self.maden_buton.SetParent(self)
        self.maden_buton.SetPosition(245, 172)
        self.maden_buton.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.maden_buton.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.maden_buton.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.maden_buton.SetToggleUpEvent(self.stop_maden_kazma)
        self.maden_buton.SetToggleDownEvent(self.start_maden_kazma)
        self.maden_buton.SetText('Maden Botu')
        self.maden_buton.SetToolTipText('|cFF00FF00Seçilen Madeni Otomatik Kaz|r')
        self.maden_buton.Show()




        self.dedektor_gm_button = ui.ToggleButton()
        self.dedektor_gm_button.SetParent(self)
        self.dedektor_gm_button.SetPosition(10, 143)
        self.dedektor_gm_button.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.dedektor_gm_button.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.dedektor_gm_button.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.dedektor_gm_button.SetToggleUpEvent(self.stop_gm_dedektor)
        self.dedektor_gm_button.SetToggleDownEvent(self.start_gm_dedektor)
        self.dedektor_gm_button.SetText('GM Dedektörü')
        self.dedektor_gm_button.Show()
        self.dedektor_oyuncu_button = ui.ToggleButton()
        self.dedektor_oyuncu_button.SetParent(self)
        self.dedektor_oyuncu_button.SetPosition(110, 143)
        self.dedektor_oyuncu_button.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.dedektor_oyuncu_button.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.dedektor_oyuncu_button.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.dedektor_oyuncu_button.SetToggleUpEvent(self.stop_player_dedektor)
        self.dedektor_oyuncu_button.SetToggleDownEvent(self.start_player_dedektor)
        self.dedektor_oyuncu_button.SetText('Oyuncu Dedektörü')
        self.dedektor_oyuncu_button.Show()

        self.pot_button = ui.ToggleButton()
        self.pot_button.SetParent(self)
        self.pot_button.SetPosition(10, 39)
        self.pot_button.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.pot_button.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.pot_button.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.pot_button.SetToggleUpEvent(self.stop_autopot)
        self.pot_button.SetToggleDownEvent(self.start_autopot)
        self.pot_button.SetText('Oto Pot')
        self.pot_button.SetToolTipText('|cFF00FF00Otomatik Kırmızı ve Mavi Pot Kullan|r')
        self.pot_button.Show()

        self.pelerin_button = ui.ToggleButton()
        self.pelerin_button.SetParent(self)
        self.pelerin_button.SetPosition(10, 73)
        self.pelerin_button.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.pelerin_button.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.pelerin_button.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.pelerin_button.SetToggleUpEvent(self.stop_otopelerin)
        self.pelerin_button.SetToggleDownEvent(self.start_otopelerin)
        self.pelerin_button.SetText('Oto Pelerin')
        self.pelerin_button.Show()
        self.metin_secenek = self.comp.ComboBox(self, 'Metine Nasil Gitsin', 110, 178, 100)
        for index, item in enumerate(metin_secenek_list):
            self.metin_secenek.InsertItem(index, str(item))

        self.secilen_maden = self.comp.ComboBox(self, 'Maden Sec', 235, 140, 105)
        for index, item in enumerate(maden):
            self.secilen_maden.InsertItem(index, str(item))


        self.pelerinler = self.comp.ComboBox(self, 'Pelerin Sec', 110, 75, 60)
        for index, item in enumerate(pelerinlist):
            self.pelerinler.InsertItem(index, str(item))
        self.pelerin_suresi = self.comp.ComboBox(self, 'Pelerin Süresi', 177, 75, 60)
        for index, item in enumerate(pelerin_sureleri):
            self.pelerin_suresi.InsertItem(index, str(item))   


        self.buff_button = ui.ToggleButton()
        self.buff_button.SetParent(self)
        self.buff_button.SetPosition(10, 392)
        self.buff_button.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.buff_button.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.buff_button.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.buff_button.SetToggleUpEvent(self.stop_otobuff)
        self.buff_button.SetToggleDownEvent(self.start_otobuff)
        self.buff_button.SetText('Oto Yeşil/Mor Pot')
        self.buff_button.Show()

        self.otobuff_button = ui.Button()
        self.otobuff_button.SetParent(self)
        self.otobuff_button.SetPosition(10, 420)
        self.otobuff_button.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.otobuff_button.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.otobuff_button.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.otobuff_button.SetEvent(lambda:self.buffbot.showbuff())
        self.otobuff_button.SetText('BuffBot')
        self.otobuff_button.Show()

        self.otohit_button = ui.ToggleButton()
        self.otohit_button.SetParent(self)
        self.otohit_button.SetPosition(60, 420)
        self.otohit_button.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.otohit_button.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.otohit_button.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.otohit_button.SetToggleUpEvent(lambda:self.stop_oto_hit())
        self.otohit_button.SetToggleDownEvent(lambda:self.start_oto_hit())
        self.otohit_button.SetText('Oto Atak')
        self.otohit_button.Show()

        self.otohit_okcu_button = ui.ToggleButton()
        self.otohit_okcu_button.SetParent(self)
        self.otohit_okcu_button.SetPosition(110, 420)
        self.otohit_okcu_button.SetUpVisual('d:/ymir work/ui/public/small_button_01.sub')
        self.otohit_okcu_button.SetOverVisual('d:/ymir work/ui/public/small_button_02.sub')
        self.otohit_okcu_button.SetDownVisual('d:/ymir work/ui/public/small_button_03.sub')
        self.otohit_okcu_button.SetToggleUpEvent(lambda:self.stop_oto_okcu())
        self.otohit_okcu_button.SetToggleDownEvent(lambda:self.start_oto_okcu())
        self.otohit_okcu_button.SetText('Okçu Atak')
        self.otohit_okcu_button.Show()

        self.pickup_button = ui.ToggleButton()
        self.pickup_button.SetParent(self)
        self.pickup_button.SetPosition(10, 109)
        self.pickup_button.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.pickup_button.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.pickup_button.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.pickup_button.SetToggleUpEvent(self.stop_ototoplama)
        self.pickup_button.SetToggleDownEvent(self.start_ototoplama)
        self.pickup_button.SetText('Oto Topla')
        self.pickup_button.SetToolTipText('|cFF00FF00Yerdeki Itemleri Otomatik Toplar|r')
        self.pickup_button.Show()

        self.oto_av_buton = ui.ToggleButton()
        self.oto_av_buton.SetParent(self)
        self.oto_av_buton.SetPosition(10, 318)
        self.oto_av_buton.SetUpVisual('d:/ymir work/ui/public/large_button_01.sub')
        self.oto_av_buton.SetOverVisual('d:/ymir work/ui/public/large_button_02.sub')
        self.oto_av_buton.SetDownVisual('d:/ymir work/ui/public/large_button_03.sub')
        self.oto_av_buton.SetToggleUpEvent(self.stop_oto_av)
        self.oto_av_buton.SetToggleDownEvent(self.start_oto_av)
        self.oto_av_buton.SetText('Otomativ Av')
        self.oto_av_buton.SetToolTipText('|cFF00FF00Otomatik Avlan|r')
        self.oto_av_buton.Show()

        self.sat = ui.ToggleButton()
        self.sat.SetParent(self)
        self.sat.SetPosition(275, 416)
        self.sat.SetUpVisual('d:/ymir work/ui/public/middle_button_01.sub')
        self.sat.SetOverVisual('d:/ymir work/ui/public/middle_button_02.sub')
        self.sat.SetDownVisual('d:/ymir work/ui/public/middle_button_03.sub')
        self.sat.SetToggleUpEvent(self.stop_otomatiksat)
        self.sat.SetToggleDownEvent(self.start_otomatiksat)
        self.sat.SetText('Otomatik Sat')
        self.sat.SetToolTipText('|cFF00FF00Gereksizleri Otomatik Sat|r')
        self.sat.Show()
        self.sebnem = ui.ToggleButton()
        self.sebnem.SetParent(self)
        self.sebnem.SetPosition(215, 416)
        self.sebnem.SetUpVisual('d:/ymir work/ui/public/middle_button_01.sub')
        self.sebnem.SetOverVisual('d:/ymir work/ui/public/middle_button_02.sub')
        self.sebnem.SetDownVisual('d:/ymir work/ui/public/middle_button_03.sub')
        self.sebnem.SetToggleDownEvent(self.sebnem_ac)
        self.sebnem.SetToggleUpEvent(self.sebnem_kapa)
        self.sebnem.SetText('Şebnemler')
        self.sebnem.SetToolTipText('|cFF00FF00Mavi,Pembe,Kırmızı Şebnem ve ET Saldırısı|r')
        self.sebnem.Show()
        self.Show()

    def Close(self):
        self.Hide()
        baslat.Show()
        return True
    def test1_func(self):
        pass
    def start_oto_hit(self):
        player.SetAttackKeyState(TRUE)
    def stop_oto_hit(self):
        player.SetAttackKeyState(FALSE)
    def update_timer_display(self):
        pass
    def sebnem_ac(self):
        self.sebnem_func()
        timer_manager.add_timer("sebnem",60,self.sebnem_func)
    def sebnem_kapa(self):
        timer_manager.remove_timer("sebnem")
    def sebnem_func(self):
        for i in range(180):
            item_id = player.GetItemIndex(i)
            if item_id==71028:
                net.SendItemUsePacket(i)
            if item_id==50821:
                net.SendItemUsePacket(i)
            if item_id==50822:
                net.SendItemUsePacket(i)
            if item_id==50825:
                net.SendItemUsePacket(i)


    def eldiven_ac(self):
        self.eldiven_func()
        timer_manager.add_timer("eldiven",60,self.eldiven_func)

    def eldiven_kapa(self):
        timer_manager.remove_timer("eldiven")
    def eldiven_func(self):
        eldiven_id = 70043  # Hırsız Eldiveni'nin ID'si
        eldiven_slot = -1
        bos_slot = -1

        # Envanterde eldiveni bul ve stack sayısını kontrol et
        for i in range(180):  # Envanterdeki tüm slotları tara
            item_id = player.GetItemIndex(i)
            if item_id == eldiven_id:
                eldiven_slot = i
                stack_count = player.GetItemCount(i)  # Stack sayısını al
                if stack_count > 1:
                    # Boş bir slot bul
                    for j in range(180):
                        if player.GetItemIndex(j) == 0:  # Boş slot
                            bos_slot = j
                            break
                    if bos_slot != -1:
                        # 1 tane ayır ve boş slota taşı
                        net.SendItemMovePacket(eldiven_slot, bos_slot, 1)
                        net.SendItemUsePacket(bos_slot)  # Yeni slottaki eldiveni kullan
                    else:
                        chat.AppendChat(2, "Boş envanter slotu bulunamadı.")
                else:
                    # Stack sayısı 1 ise doğrudan kullan
                    net.SendItemUsePacket(eldiven_slot)
                return


    def start_check_dead(self):
        """10 saniyede bir ölü olup olmadığını kontrol eder"""
        self.metin_bot.check_dead()
        timer_manager.add_timer("check_dead", 10.0, self.start_check_dead)

    def show_saved_coordinates(self):
        """ Kaydedilen koordinatları metin_koord EditLine'da gösterir """
        coordinates_text = ""
        for x, y in self.metin_bot.route:
            coordinates_text += "{}, {}\n".format(x, y)
        self.metin_koord_value.SetText(coordinates_text)
    def start_otomatiksat(self):
        timer_manager.add_timer("otomatiksat", 0.1, self.otomatiksat_func)  # 5 saniyede bir çalıştır

    def stop_otomatiksat(self):
        timer_manager.remove_timer("otomatiksat")  # Otomatik satmayı durdur

    def otomatiksat_func(self):
        for i in range(180):
            item_id = player.GetItemIndex(i)
            if item_id in idler:
                net.SendItemSellPacket(i)
                break

    def start_autopot(self):
        timer_manager.add_timer("pot", 0.5, self.use_potion)

    def stop_autopot(self):
        timer_manager.remove_timer("pot")  # Oto potu durdur

    def use_potion(self):
        maxhp = player.GetStatus(player.MAX_HP)
        current_hp = player.GetStatus(player.HP)

        if (float(current_hp) / float(maxhp)) * 100 < 90:
            for i in range(90):
                item_id = player.GetItemIndex(i)
                if item_id in [27001, 1940, 27003]:
                    net.SendItemUsePacket(i)
                    break

        maxmp = player.GetStatus(player.MAX_SP)
        current_mp = player.GetStatus(player.SP)

        if (float(current_mp) / float(maxmp)) * 100 < 90:
            for i in range(90):
                item_id = player.GetItemIndex(i)
                if item_id in [27004, 1941, 27006]:
                    net.SendItemUsePacket(i)
                    break

    def start_check_connection(self):
        """ Bağlantı kontrolünü 5 saniyede bir çalıştırır """
        timer_manager.add_timer("check_connection", 5.0, self.connection_func)

    def stop_check_connection(self):
        """ Bağlantı kontrolünü durdurur """
        timer_manager.remove_timer("check_connection")

    def connection_func(self):
        if not net.IsConnect():
            self.stop_all_actions()
        else:
           pass

    def otosandik_ac(self):
        timer_manager.add_timer("sandik", 1.0, self.use_sandik)

    def otosandik_kapa(self):
        timer_manager.remove_timer("sandik")

    def use_sandik(self):
        gold_chest_slot = []
        silver_chest_slot = []
        gold_key_slot = []
        silver_key_slot = []

        for i in range(180):
            item_id = player.GetItemIndex(i)
            if item_id == 50006:
                gold_chest_slot.append(i)
            elif item_id == 50007:
                silver_chest_slot.append(i)
            elif item_id == 50008:
                gold_key_slot.append(i)
            elif item_id == 50009:
                silver_key_slot.append(i)

        # Altın sandık ve anahtarları kullan
        if len(gold_chest_slot) > 0 and len(gold_key_slot) > 0:
            for i in range(min(len(gold_chest_slot), len(gold_key_slot))):
                net.SendItemUseToItemPacket(gold_key_slot[i], gold_chest_slot[i])

        # Gümüş sandık ve anahtarları kullan
        if len(silver_chest_slot) > 0 and len(silver_key_slot) > 0:
            for i in range(min(len(silver_chest_slot), len(silver_key_slot))):
                net.SendItemUseToItemPacket(silver_key_slot[i], silver_chest_slot[i])

        # Fazla altın sandık ve anahtarları sat
        for i in range(2, len(gold_chest_slot)):
            net.SendItemSellPacket(gold_chest_slot[i])
        for i in range(2, len(gold_key_slot)):
            net.SendItemSellPacket(gold_key_slot[i])

        # Fazla gümüş sandık ve anahtarları sat
        for i in range(2, len(silver_chest_slot)):
            net.SendItemSellPacket(silver_chest_slot[i])
        for i in range(2, len(silver_key_slot)):
            net.SendItemSellPacket(silver_key_slot[i])
    def start_oto_okcu(self):
        global sabit_koord
        sabit_koord=player.GetMainCharacterIndex()
        timer_manager.add_timer("okcu",1.0,self.okcu_oto_func)
    def stop_oto_okcu(self):
        timer_manager.remove_timer("okcu")
        player.SetAttackKeyState(FALSE)
    def okcu_oto_func(self):
        global sabit_koord
        chr.SetPixelPosition(sabit_koord)
        o = player.GetMainCharacterIndex()
        closest_enemy = None
        closest_distance = float('inf')  # Başlangıçta en uzak mesafe

        # **Düşmanları Manuel Tara**
        for i in xrange(o - 500000, o + 50000):  # ID aralığını geniş tut
            if chr.IsEnemy(i):  # Eğer düşmansa
                mesafe = player.GetCharacterDistance(i)
                if 0 < mesafe < 2150:  # 2150 menzil içinde mi?
                    if mesafe < closest_distance:  # En yakını kontrol et
                        closest_distance = mesafe
                        closest_enemy = i

                # **Eğer en yakın düşman bulunduysa saldır**
                if closest_enemy:
                    player.SetTarget(closest_enemy)  # Hedefi belirle
                    if closest_distance < 200:  # Yaklaşınca saldır
                        player.SetTarget(closest_enemy)
                        chr.SelectInstance(closest_enemy)
                        player.SetAttackKeyState(TRUE)
    def start_otopelerin(self):
        sure=self.pelerin_suresi.GetCurrentText()
        if sure=="Pelerin Süresi":
            chat.AppendChat(2,"Pelerini kullanmak için bir süre seçin!")
        else:

            timer_manager.add_timer("pelerin", int(sure), self.use_pelerin)  # 1 saniyede bir çalıştır

    def stop_otopelerin(self):
        timer_manager.remove_timer("pelerin")  # Oto pelerini durdur
      
    def use_pelerin(self):
        global pelerin_sayac
        myVid = player.GetMainCharacterIndex()
        pelerin_id = -1
        secili_pelerin = self.pelerinler.GetCurrentText()
        if secili_pelerin == 'Pelerin Sec':
            chat.AppendChat(2, "Pelerin Seçiniz")
            self.stop_otopelerin()
            return

        pelerin_bulundu = False  # Pelerin bulunup bulunmadığını takip etmek için

        for i in xrange(90):
            a = player.GetItemIndex(i)
            if secili_pelerin == 'Normal' and a == 70038:
                net.SendItemUsePacket(i)
                pelerin_bulundu = True
                break  # Pelerin bulunduğunda döngüden çık
            elif secili_pelerin == 'Archer' and a == 71273:
                net.SendItemUsePacket(i)
                pelerin_bulundu = True
                break  # Pelerin bulunduğunda döngüden çık
        #if not pelerin_bulundu:
            #chat.AppendChat(2, "Pelerin Bulunamadı")

        if secili_pelerin== 'Mega Archer':
            timer_manager.remove_timer("pelerin")
            timer_manager.add_timer("pelerin", 0.1, self.use_pelerin)
            for i in xrange(90):
                b=player.GetItemIndex(i)
                if b==71273:
                    pelerin_id=i
            a=1
            while a:
                x, y, z = player.GetMainCharacterPosition()
                if pelerin_sayac == 0:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) - 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 1:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) - 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 2:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) + 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 3:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) + 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 4:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) - 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 5:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) - 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 6:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) + 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 7:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) + 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 8:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) + 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 9:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) + 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 10:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) - 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 11:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) - 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 12:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) + 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 13:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) + 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 14:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) - 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 15:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) - 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac = 0
                    timer_manager.remove_timer("pelerin")
                    timer_manager.add_timer("pelerin",10.0,self.use_pelerin)
                    continue
        if secili_pelerin== 'Mega Pelerin':
            timer_manager.remove_timer("pelerin")
            timer_manager.add_timer("pelerin", 0.1, self.use_pelerin)
            for i in xrange(90):
                b=player.GetItemIndex(i)
                if b==70038:
                    pelerin_id=i
            a=1
            while a:
                x, y, z = player.GetMainCharacterPosition()
                if pelerin_sayac == 0:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) - 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 1:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) - 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 2:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) + 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 3:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) + 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 4:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) - 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 5:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) - 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 6:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) + 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 7:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) + 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 8:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) + 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 9:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) + 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 10:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) - 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 11:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x) - 2000, int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 12:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) + 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 13:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) + 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 14:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) - 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    a = 0
                    pelerin_sayac+=1
                    continue
                if pelerin_sayac == 15:
                    chr.SelectInstance(myVid)
                    chr.SetPixelPosition(int(x), int(y) - 2000, int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    net.SendItemUsePacket(pelerin_id)
                    net.SendItemUsePacket(pelerin_id)
                    a = 0
                    pelerin_sayac = 0
                    timer_manager.remove_timer("pelerin")
                    timer_manager.add_timer("pelerin",10.0,self.use_pelerin)
                    continue
       

              
    def teleport(self):
        oyuncu_konumu = player.GetMainCharacterIndex()
        x_koord = self.metin_koord_value.GetText()
        y_koord = self.metin_koord_value2.GetText()
        try:
            chr.SetPixelPosition(int(x_koord)*100,int(y_koord)*100,100)
            player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
            player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
        except Exception as e:
            chat.AppendChat(2,'Hata : '+e)

              
    def start_otobuff(self):
        timer_manager.add_timer("buff", 10.0, self.use_buff)  # 50 saniyede bir çalıştır

    def stop_otobuff(self):
        timer_manager.remove_timer("buff")  # Oto buffı durdur

    def use_buff(self):
        for i in xrange(90):
            a = player.GetItemIndex(i)
            if a == 27102:
                net.SendItemUsePacket(i)
            if a == 27105:
                net.SendItemUsePacket(i)
            if a == 71004:
                net.SendItemUsePacket(i)               
                break
 

    def start_ototoplama(self):
        timer_manager.add_timer("pickup", 1.0, self.use_toplama)  # 1 saniyede bir çalıştır

    def stop_ototoplama(self):
        timer_manager.remove_timer("pickup")  # Oto toplamayı durdur

    def use_toplama(self):
        player.PickCloseItem()

    def start_maden_kazma(self):
        
        timer_manager.add_timer("madenci", 30, self.maden_func)
        self.maden_func()

    def stop_maden_kazma(self):
        timer_manager.remove_timer("madenci")  # Oto maden kazmayı durdur

    def maden_func(self):
        o = player.GetMainCharacterIndex()
        secilen_maden = self.secilen_maden.GetCurrentText()
        if secilen_maden == 'Maden Sec':
            chat.AppendChat(2, "Maden Seçiniz")
            self.stop_maden_kazma()
        elif secilen_maden == 'Elmas Damari':
            maden_id=20047
        elif secilen_maden == 'Ruh Kristali':
            maden_id=30301
        elif secilen_maden == 'Fosillesmis Agac Damari':
            maden_id=20049
        elif secilen_maden == 'Bakir Damari':
            maden_id=20050
        elif secilen_maden == 'Gumus Damari':
            maden_id=20051
        elif secilen_maden == 'Altin Damari':
            maden_id=20052
        elif secilen_maden == 'Zumrut Damari':
            maden_id=30304
        elif secilen_maden == 'Abanoz Damari':
            maden_id=20054
        elif secilen_maden == 'Inci Damari':
            maden_id=20055
        elif secilen_maden == 'Beyaz Altin Damari':
            maden_id=20056
        elif secilen_maden == 'Kristal Damari':
            maden_id=20057
        elif secilen_maden == 'Grena Damari':
            maden_id=30303
        elif secilen_maden == 'Ametist Damari':
            maden_id=20058
        elif secilen_maden == 'Cennetin Gozu Damari':
            maden_id=20059
        for i in xrange(o-50000, o+52000):
            dystans = player.GetCharacterDistance(i)
            Typ = chr.GetInstanceType(i)
            if Typ==1:
                chr.SelectInstance(i)
                Race=chr.GetRace(i)
                if Race==maden_id:
                    if dystans < 150:
                        
                        net.SendOnClickPacket(i)
                        break
                    else:
                        #(X, Y, Z) = chr.GetPixelPosition(i)
                        #oyuncu_konumu = player.GetMainCharacterIndex()
                        #chr.MoveToDestPosition(oyuncu_konumu,X,Y)
                        
                        pass
				        


    def start_gm_dedektor(self):
        timer_manager.add_timer("gmdedektor", 0.8, self.use_gmdedektor)  # 1 saniyede bir çalıştır

    def stop_gm_dedektor(self):
        timer_manager.remove_timer("gmdedektor")  # Oto potu durdur

    def use_gmdedektor(self):
        o = player.GetMainCharacterIndex()
        for i in xrange(o - 50000, o + 100000):
            if chr.IsGameMaster(i):
                menzil = player.GetCharacterDistance(i)
                isim = chr.GetNameByVID(i)
                snd.PlaySound("gm.wav")
                dbg.LogBox(str(isim) + '  ' + str(int(menzil)))
                timer_manager.remove_timer("gmdedektor")
                
        
    def start_player_dedektor(self):
        timer_manager.add_timer("playerdedektor", 0.8, self.use_playerdedektor)  # 1 saniyede bir çalıştır

    def stop_player_dedektor(self):
        timer_manager.remove_timer("playerdedektor") 

    def use_playerdedektor(self):
        o = player.GetMainCharacterIndex()
        ana_oyuncu_vid = net.GetMainActorVID()  # Ana karakterin VID'sini al

        for i in xrange(o - 50000, o + 100000):
            if chr.HasInstance(i):
                a = chr.GetInstanceType(i)
                if a == 6:  # Eğer bir oyuncuysa
                    oyuncu_vid = i

                    if oyuncu_vid != ana_oyuncu_vid:  # Ana karakter değilse
                        oyuncu_adi = chr.GetNameByVID(i)
                        chat.AppendChat(2, "Yanınızda bir oyuncu var. Dikkat! Oyuncu: {}".format(oyuncu_adi))
                        snd.PlaySound("oyuncu.wav")
                    
                        # Tüm işlemleri durdur
                        self.stop_all_actions()
                    
                        timer_manager.remove_timer("playerdedektor")  
                        timer_manager.add_timer("playerdedektor", 10.0, self.use_playerdedektor) 

                        
                        

    def stop_all_actions(self):
        """Tüm işlemleri durdurur"""
        self.stop_follow_route()
        self.stop_metinbot()
        self.stop_oto_av()
        self.stop_ototoplama()
        self.stop_maden_kazma()
        self.stop_autopot()
        self.stop_otopelerin()
        self.stop_otobuff()
        self.stop_otomatiksat()
        self.metin_bot.disable_skills()
        player.SetAttackKeyState(FALSE)
        chat.AppendChat(2, "Yakınınızda bir oyuncu olduğu için tüm işlemler durduruldu.")
  

    def start_route(self):
        self.metin_bot.start_bot()

    def stop_route(self):
        self.metin_bot.stop_bot()  # MetinBot'u durdur
        player.SetAttackKeyState(FALSE)

    def start_metinbot(self):
        timer_manager.add_timer("metin", 0.8, self.oto_metin_func)  # 1 saniyede bir çalıştır

    def stop_metinbot(self):
        timer_manager.remove_timer("metin")  # Oto potu durdur
        player.SetAttackKeyState(FALSE)

    def vurus_timer(self):
        timer_manager.add_timer("vurusmodu",3.0,self.vurus_modu)
    def vurus_kapa(self):
        timer_manager.remove_timer("vurusmodu")
    def vurus_modu(self):
        secilen_mod=self.modlar.GetCurrentText()
        if secilen_mod=="Tek El" or secilen_mod=="Vurus Modu Sec":
            chr.SetMotionMode(chr.MOTION_MODE_ONEHAND_SWORD)
        elif secilen_mod=="Cift El":
            chr.SetMotionMode(chr.MOTION_MODE_TWOHAND_SWORD)
        elif secilen_mod=="Yay":
            chr.SetMotionMode(chr.MOTION_MODE_BOW)
        elif secilen_mod=="Bicak":
            chr.SetMotionMode(chr.MOTION_MODE_DUALHAND_SWORD)
        elif secilen_mod=="Yelpaze":
            chr.SetMotionMode(chr.MOTION_MODE_FAN)
        elif secilen_mod=="Can":
            chr.SetMotionMode(chr.MOTION_MODE_BELL)
        elif secilen_mod=="Atta Tek El":
            chr.SetMotionMode(chr.MOTION_MODE_HORSE_ONEHAND_SWORD)
        elif secilen_mod=="Atta Cift El":
            chr.SetMotionMode(chr.MOTION_MODE_HORSE_TWOHAND_SWORD)
        elif secilen_mod=="Atta Yay":
            chr.SetMotionMode(chr.MOTION_MODE_HORSE_BOW)
        elif secilen_mod=="Atta Bicak":
            chr.SetMotionMode(chr.MOTION_MODE_HORSE_DUALHAND_SWORD)
        elif secilen_mod=="Atta Can":
            chr.SetMotionMode(chr.MOTION_MODE_HORSE_BELL)
        elif secilen_mod=="Olta":
            chr.SetMotionMode(chr.MOTION_MODE_FISHING)
        elif secilen_mod=="Yumruk":
            chr.SetMotionMode(chr.MOTION_MODE_GENERAL)

    def kombo_ac(self):
        chr.testSetComboType(2)

    def normal_demirci(self):
        net.SendRefinePacket(0, 0)

    def kule_demircisi(self):
        net.SendRefinePacket(0, 4)

    def kombo_kapa(self):
        chr.testSetComboType(0)

    def oto_metin_func(self):
        global degisken
        player.SetAttackKeyState(FALSE)
        metine_gitme_yolu = self.metin_secenek.GetCurrentText()
        o = player.GetMainCharacterIndex()
        degisken += 5

        closest_metin = None
        closest_distance = float('inf')  # Başlangıçta en uzak mesafe
        if metine_gitme_yolu == 'Metine Nasil Gitsin':
            chat.AppendChat(2, "Metne Nasıl Gitmek İstediğinizi Seçin")
            self.stop_metinbot()
            return
        elif metine_gitme_yolu=='Yuru':
                       # **Metinleri Manuel Tara**
            for i in xrange(o - 500000, o + 50000):  # ID aralığını geniş tut
                a = chr.GetInstanceType(i)
                if a == 2:
                    mesafe = player.GetCharacterDistance(i)
                    if 0 < mesafe < 5000:  # 5.000 menzil içinde mi?
                        if mesafe < closest_distance:  # En yakını kontrol et
                            closest_distance = mesafe
                            closest_metin = i

                # **Eğer en yakın metin bulunduysa saldır**
                if closest_metin:
                    player.SetTarget(closest_metin)  # Hedefi belirle
                    x, y, z = chr.GetPixelPosition(closest_metin)
                    chr.MoveToDestPosition(o, int(x), int(y))
                    net.SendOnClickPacket(closest_metin)
                    player.SetAttackKeyState(TRUE)
                    return

        elif metine_gitme_yolu=='Isinlan':
                                   # **Metinleri Manuel Tara**
            for i in xrange(o - 500000, o + 50000):  # ID aralığını geniş tut
                a = chr.GetInstanceType(i)
                if a == 2:
                    player.PickCloseItem()
                    x, y, z = chr.GetPixelPosition(i)
                    chr.SetPixelPosition(int(x), int(y), int(z))
                    player.SetSingleDIKKeyState(app.DIK_UP, TRUE)
                    player.SetSingleDIKKeyState(app.DIK_UP, FALSE)
                    player.SetAttackKeyState(TRUE)
                    net.SendOnClickPacket(i)
                    player.PickCloseItem()
                    return


    def start_oto_av(self):
        timer_manager.add_timer("av", 0.6, self.oto_av)  # 1 saniyede bir çalıştır

    def stop_oto_av(self):
        timer_manager.remove_timer("av")  # Oto potu durdur
        player.SetAttackKeyState(FALSE)


    def oto_av(self):
        global degisken
        player.SetAttackKeyState(FALSE)

        o = player.GetMainCharacterIndex()
        degisken += 5

        closest_enemy = None
        closest_distance = float('inf')  # Başlangıçta en uzak mesafe

        # **Düşmanları Manuel Tara**
        for i in xrange(o - 500000, o + 50000):  # ID aralığını geniş tut
            if chr.IsEnemy(i):  # Eğer düşmansa
                mesafe = player.GetCharacterDistance(i)
                if 0 < mesafe < 5000:  # 5.000 menzil içinde mi?
                    if mesafe < closest_distance:  # En yakını kontrol et
                        closest_distance = mesafe
                        closest_enemy = i

                # **Eğer en yakın düşman bulunduysa saldır**
                if closest_enemy:
                    player.SetTarget(closest_enemy)  # Hedefi belirle
                    x, y, z = chr.GetPixelPosition(closest_enemy)
                    chr.MoveToDestPosition(o, int(x), int(y))  # Düşmana git

                    if closest_distance < 200:  # Yaklaşınca saldır
                        player.SetTarget(closest_enemy)
                        chr.SelectInstance(o)
                        player.SetAttackKeyState(TRUE)
                        rnd = app.GetRandom(0, 7)
                        chr.SetDirection(rnd)

    def Close(self):
        self.Hide()


class Component:
	def Button(self, parent, buttonName, tooltipText, x, y, func, UpVisual, OverVisual, DownVisual):
		button = ui.Button()
		if parent != None:
			button.SetParent(parent)
		button.SetPosition(x, y)
		button.SetUpVisual(UpVisual)
		button.SetOverVisual(OverVisual)
		button.SetDownVisual(DownVisual)
		button.SetText(buttonName)
		button.SetToolTipText(tooltipText)
		button.Show()
		button.SetEvent(func)
		return button

	def ToggleButton(self, parent, buttonName, tooltipText, x, y, funcUp, funcDown, UpVisual, OverVisual, DownVisual):
		button = ui.ToggleButton()
		if parent != None:
			button.SetParent(parent)
		button.SetPosition(x, y)
		button.SetUpVisual(UpVisual)
		button.SetOverVisual(OverVisual)
		button.SetDownVisual(DownVisual)
		button.SetText(buttonName)
		button.SetToolTipText(tooltipText)
		button.Show()
		button.SetToggleUpEvent(funcUp)
		button.SetToggleDownEvent(funcDown)
		return button


	def EditLine(self, parent, editlineText, x, y, width, heigh, max):
		SlotBar = ui.SlotBar()
		if parent != None:
			SlotBar.SetParent(parent)
		SlotBar.SetSize(width, heigh)
		SlotBar.SetPosition(x, y)
		SlotBar.Show()
		Value = ui.EditLine()
		Value.SetParent(SlotBar)
		Value.SetSize(width, heigh)
		Value.SetPosition(5, 1)
		Value.SetMax(max)
		Value.SetLimitWidth(width)
		Value.SetMultiLine()
		Value.SetText(editlineText)
		Value.Show()
		return SlotBar, Value

	def TextLine(self, parent, textlineText, x, y, color):
		textline = ui.TextLine()
		if parent != None:
			textline.SetParent(parent)
		textline.SetPosition(x, y)
		if color != None:
			textline.SetFontColor(color[0], color[1], color[2])
		textline.SetText(textlineText)
		textline.Show()
		return textline

	def SlotbarText(self, parent, editlineText, x, y, width, heigh):
		SlotBar = ui.SlotBar()
		SlotBar.SetParent(parent)
		SlotBar.SetSize(width, heigh)
		SlotBar.SetPosition(x, y)
		SlotBar.Show()
		TextLine = ui.TextLine()
		TextLine.SetParent(SlotBar)
		TextLine.SetText(editlineText)
		TextLine.SetHorizontalAlignCenter()
		TextLine.SetVerticalAlignCenter()
		TextLine.SetWindowHorizontalAlignCenter()
		TextLine.SetWindowVerticalAlignCenter()
		TextLine.Show()
		return SlotBar, TextLine


	def RGB(self, r, g, b):
		return (r*255, g*255, b*255)

	def SliderBar(self, parent, sliderPos, func, x, y):
		Slider = ui.SliderBar()
		if parent != None:
			Slider.SetParent(parent)
		Slider.SetPosition(x, y)
		Slider.SetSliderPos(sliderPos / 100)
		Slider.Show()
		Slider.SetEvent(func)
		return Slider

	def ExpandedImage(self, parent, x, y, img):
		image = ui.ExpandedImageBox()
		if parent != None:
			image.SetParent(parent)
		image.SetPosition(x, y)
		image.LoadImage(img)
		image.Show()
		return image

	def ComboBox(self, parent, text, x, y, width):
		combo = ui.ComboBox()
		if parent != None:
			combo.SetParent(parent)
		combo.SetPosition(x, y)
		combo.SetSize(width, 15)
		combo.SetCurrentItem(text)
		combo.Show()
		return combo

	def ThinBoard(self, parent, moveable, x, y, width, heigh, center):
		thin = ui.ThinBoard()
		if parent != None:
			thin.SetParent(parent)
		if moveable == TRUE:
			thin.AddFlag('movable')
			thin.AddFlag('float')
		thin.SetSize(width, heigh)
		thin.SetPosition(x, y)
		if center == TRUE:
			thin.SetCenterPosition()
		thin.Show()
		return thin

	def Gauge(self, parent, width, color, x, y):
		gauge = ui.Gauge()
		if parent != None:
			gauge.SetParent(parent)
		gauge.SetPosition(x, y)
		gauge.MakeGauge(width, color)
		gauge.Show()
		return gauge

	def ListBoxEx(self, parent, x, y, width, heigh, SVIC, event):
		bar = ui.Bar()
		if parent != None:
			bar.SetParent(parent)
		bar.SetPosition(x, y)
		bar.SetSize(width, heigh)
		bar.SetColor(0x77000000)
		bar.Show()
		ListBox=ui.ListBoxEx()
		ListBox.SetParent(bar)
		ListBox.SetPosition(0, 0)
		ListBox.SetSize(width, heigh)
		ListBox.SetViewItemCount(SVIC)
		ListBox.SetSelectEvent(event)
		ListBox.Show()
		scroll = ui.ScrollBar()
		scroll.SetParent(ListBox)
		scroll.SetPosition(width-15, 0)
		scroll.SetScrollBarSize(heigh)
		scroll.Show()
		ListBox.SetScrollBar(scroll)
		return bar, ListBox

	def ListBoxEx1(self, parent, x, y, width, heigh, SVIC, event):
		bar = ui.Bar()
		if parent != None:
			bar.SetParent(parent)
		bar.SetPosition(x, y)
		bar.SetSize(width, heigh)
		bar.SetColor(0x77000000)
		bar.Show()
		ListBox=ui.ListBoxEx()
		ListBox.SetParent(bar)
		ListBox.SetPosition(0, 0)
		ListBox.SetSize(width, heigh)
		ListBox.SetViewItemCount(SVIC)
		ListBox.SetSelectEvent(event)
		ListBox.Show()
		scroll = ui.ScrollBar()
		scroll.SetParent(ListBox)
		scroll.SetPosition(width-15, 0)
		scroll.SetScrollBarSize(heigh)
		scroll.Show()
		ListBox.SetScrollBar(scroll)
		return bar, ListBox

	def HorizontalBar(self, parent, x, y, Create):
		horizontalBar = ui.HorizontalBar()
		if parent != None:
			horizontalBar.SetParent(parent)
		horizontalBar.SetPosition(x, y)
		horizontalBar.Create(Create)
		horizontalBar.Show()
		return horizontalBar

	def GetCurrentText(self):
		return self.textLine.GetText()
	def OnSelectItem(self, index, name):
		self.SetCurrentItem(name)
		self.CloseListBox()
		self.event()
	ui.ComboBox.GetCurrentText = GetCurrentText
	ui.ComboBox.OnSelectItem = OnSelectItem
class Item(ui.ListBoxEx.Item):
	def __init__(self, text):
		ui.ListBoxEx.Item.__init__(self)
		self.canLoad=0
		self.text=text
		self.textLine=self.__CreateTextLine(text[:1000])
	def __del__(self):
		ui.ListBoxEx.Item.__del__(self)
	def GetText(self):
		return self.text
	def SetSize(self, width, height):
		ui.ListBoxEx.Item.SetSize(self,2*len(self.textLine.GetText()) + 35, height)
	def __CreateTextLine(self, text):
		textLine=ui.TextLine()
		textLine.SetParent(self)
		textLine.SetPosition(0, 0)
		textLine.SetText(text)
		textLine.Show()
		return textLine
class BuffBot(ui.BoardWithTitleBar):
    def __init__(self):
        ui.BoardWithTitleBar.__init__(self)
        self.comp = Component()
        self.BuildBuffWindow()
        self.select_vid_timer_key = "select_vid_timer"
        self.skill_timer_key = "skill_timer"
        self.skill_step = 0
        
    def __del__(self):
        ui.BoardWithTitleBar.__del__(self)

    def BuildBuffWindow(self):
        self.SetSize(165, 100)
        self.SetCenterPosition()
        self.AddFlag('movable')
        self.AddFlag('float')
        self.SetTitleName('Buff için İsim Yazın')
        self.SetCloseEvent(self.Close)

        self.yuzuk = self.comp.ToggleButton(self, 'Yuzuk Kullan','|cFF00FF00Yüzüğü 1.Slota Koyun|r', 60, 35,self.yuzuk_stop, self.yuzuk_start,'d:/ymir work/ui/public/small_button_01.sub','d:/ymir work/ui/public/small_button_02.sub', 'd:/ymir work/ui/public/small_button_03.sub')

        self.select_vid_button = self.comp.ToggleButton(self, '','BuffBotu Başlat', 10, 50,self.stop_select_vid_timer, self.start_select_vid_timer,'d:/ymir work/ui/skill/shaman/hosin_02.sub','d:/ymir work/ui/skill/shaman/hosin_02.sub', 'd:/ymir work/ui/skill/shaman/hosin_03.sub')
        self.buff_isim, self.buff_isim_value = self.comp.EditLine(self, '', 50, 58, 100, 18, 13)

    def yuzuk_start(self):
        timer_manager.add_timer("yuzuk", 60.0, self.use_yuzuk)

    def yuzuk_stop(self):
        timer_manager.remove_timer("yuzuk")

    def use_yuzuk(self):
        net.SendItemUsePacket(0)
    def showbuff(self):
        self.Show()

    def select_vid(self):
        try:
            # Etraftaki tüm karakterleri kontrol et
            o = player.GetMainCharacterIndex()
            closest_vid = None
            closest_distance = float('inf')  # Başlangıçta en uzak mesafe
            hedef = self.buff_isim_value.GetText()
            for i in range(o - 50000, o + 50000):  # ID aralığını geniş tut
                if chr.HasInstance(i):
                    try:
                        name = chr.GetNameByVID(i)
                        if name == str(hedef):
                            distance = player.GetCharacterDistance(i)
                            if distance < closest_distance:
                                closest_distance = distance
                                closest_vid = i
                    except Exception as e:
                        chat.AppendChat(3, "İsim karşılaştırma hatası: " + str(e))

            if closest_vid:
                player.SetTarget(closest_vid)
                self.start_skill_timer()
                return closest_vid

            chat.AppendChat(3, str(hedef) + " bulunamadı.")
            return None
        except Exception as e:
            chat.AppendChat(3, "Hata oluştu: " + str(e))
        return None

    def start_skill_timer(self):
        self.skill_step = 0
        self.use_skills()  # İlk skili hemen kullan
        timer_manager.add_timer(self.skill_timer_key, 2.4, self.use_skills)

    def use_skills(self):

        try:
            if self.skill_step == 0:
                player.ClickSkillSlot(4)  # 4 numaralı skili kullan
                self.skill_step += 1
                timer_manager.add_timer("skill_step_1", 2.4, self.use_skills)
            elif self.skill_step == 1:
                player.ClickSkillSlot(6)  # 6 numaralı skili kullan
                self.skill_step += 1
                timer_manager.remove_timer("skill_step_1")
                timer_manager.remove_timer(self.skill_timer_key)  # Zamanlayıcıyı durdur
        except Exception as e:
            chat.AppendChat(3, "Skill kullanma hatası: " + str(e))
            timer_manager.remove_timer("skill_step_1")
            timer_manager.remove_timer(self.skill_timer_key)

    def start_select_vid_timer(self):
        timer_manager.add_timer(self.select_vid_timer_key, 2.5, self.select_vid)

    def stop_select_vid_timer(self):
        timer_manager.remove_timer(self.select_vid_timer_key)
    def Close(self):
        self.Hide()
        return True


class GameUI(ui.ScriptWindow):
    def __init__(self):
        ui.ScriptWindow.__init__(self)
        self.panel = Dialog1
        self.last_position = player.GetMainCharacterPosition()  # Son konumu sakla
        self.Show()

    def OnUpdate(self):

        timer_manager.update()  # Tüm zamanlayıcıları güncelle
        current_position = player.GetMainCharacterPosition()
        if self.detect_large_position_change(current_position):
            self.last_position = current_position
            timer_manager.restart_timers()

    def detect_large_position_change(self, current_position):
        """Büyük pozisyon değişikliklerini tespit eder"""
        last_x, last_y, last_z = self.last_position
        current_x, current_y, current_z = current_position
        distance = ((current_x - last_x) ** 2 + (current_y - last_y) ** 2) ** 0.5
        return distance > 1000  # 1000 birimden büyük değişiklikleri tespit et


game_ui = GameUI()


dialog = Dialog1()
dialog.Hide()

baslat = BaslatPaneli(dialog)
dialog.baslat_paneli = baslat
Copied