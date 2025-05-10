!include "MUI.nsh"
!include "WinMessages.nsh"

Name "Hex Cheat"
InstallDir "$PROGRAMFILES64\Hex Cheat"
OutFile "C:\Users\OEM\Desktop\Setup\Hex_Cheat_Installer.exe"
BrandingText "Hex Cheat Setup"

; Modern UI ayarları
!define MUI_ICON "setup.ico"
!define MUI_UNICON "setup.ico"

; Header ayarları
!define MUI_HEADERIMAGE
!define MUI_HEADERIMAGE_RIGHT
!define MUI_HEADERIMAGE_BITMAP "header.bmp"
!define MUI_HEADERIMAGE_UNBITMAP "header_uninstall.bmp"

; Diğer UI ayarları
!define MUI_WELCOMEFINISHPAGE_BITMAP "wizard.bmp"
!define MUI_UNWELCOMEFINISHPAGE_BITMAP "uninstall.bmp"

!define MUI_WELCOMEPAGE_TEXT "Setup will guide you through the installation process of Hex Cheat.\n\nYou should close all other applications before continuing.\n\nClick Next to continue and Cancel to exit the Setup Wizard."

!define MUI_PAGE_CUSTOMFUNCTION_PRE ComponentsPre
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_LICENSE "license.txt"
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_COMPONENTS
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH

!insertmacro MUI_UNPAGE_WELCOME
!insertmacro MUI_UNPAGE_CONFIRM
!insertmacro MUI_UNPAGE_INSTFILES
!insertmacro MUI_UNPAGE_FINISH

!insertmacro MUI_LANGUAGE "English"

Var DesktopShortcut
Var StartMenuShortcut

Function ComponentsPre
  StrCpy $DesktopShortcut 1    ; Varsayılan olarak seçili
  StrCpy $StartMenuShortcut 0   ; Varsayılan olarak seçili değil
FunctionEnd

Function RefreshShellIcons
  ; Sadece masaüstü ikonlarını yenile
  System::Call 'shell32::SHChangeNotify(i 0x08000000, i 0, i 0, i 0)'
  
  ; Masaüstü penceresini yenile
  FindWindow $0 "Progman" ""
  SendMessage $0 0x111 0x7103 0
  
  ; Icon cache'i yenile
  System::Call 'shell32::SHChangeNotify(i 0x2000, i 0x1000, t "$DESKTOP", i 0)'
FunctionEnd

!define MUI_FINISHPAGE_RUN_FUNCTION RefreshShellIcons
!define MUI_PAGE_CUSTOMFUNCTION_SHOW RefreshShellIcons

; Finish page ayarları
!define MUI_FINISHPAGE_RUN "$WINDIR\explorer.exe"
!define MUI_FINISHPAGE_RUN_PARAMETERS "/e,/root,$DESKTOP"
!define MUI_FINISHPAGE_RUN_TEXT "Refresh desktop icons"
!define MUI_FINISHPAGE_RUN_NOTCHECKED
!define MUI_FINISHPAGE_SHOWREADME ""
!define MUI_FINISHPAGE_SHOWREADME_TEXT "Refresh desktop icons"
!define MUI_FINISHPAGE_SHOWREADME_FUNCTION RefreshShellIcons

Section "Desktop Shortcut" SEC_DESKTOP
  SectionIn RO
  ${If} $DesktopShortcut == 1
    CreateShortCut "$DESKTOP\Hex Cheat.lnk" "$INSTDIR\Hex Cheat.exe" "" "$INSTDIR\Hex Cheat.exe" 0
    Call RefreshShellIcons
  ${EndIf}
SectionEnd

Section "Start Menu Shortcuts" SEC_STARTMENU
  ${If} $StartMenuShortcut == 1
    CreateDirectory "$SMPROGRAMS\Hex Cheat"
    CreateShortCut "$SMPROGRAMS\Hex Cheat\Hex Cheat.lnk" "$INSTDIR\Hex Cheat.exe" "" "$INSTDIR\Hex Cheat.exe" 0
    CreateShortCut "$SMPROGRAMS\Hex Cheat\Uninstall.lnk" "$INSTDIR\uninstall.exe" "" "$INSTDIR\uninstall.exe" 0
  ${EndIf}
SectionEnd

Section -Main
  SetOutPath $INSTDIR
  File /r "C:\Users\OEM\Desktop\Exe\*"
  
  WriteUninstaller "$INSTDIR\uninstall.exe"
  
  ; Program Ekle/Kaldır için detaylı bilgiler
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Hex Cheat" \
                   "DisplayName" "Hex Cheat"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Hex Cheat" \
                   "UninstallString" "$INSTDIR\uninstall.exe"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Hex Cheat" \
                   "DisplayIcon" "$INSTDIR\Hex Cheat.exe,0"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Hex Cheat" \
                   "Publisher" "Hex Cheat"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Hex Cheat" \
                   "DisplayVersion" "1.0"
  WriteRegDWORD HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Hex Cheat" \
                   "EstimatedSize" 100000  ; Boyut KB cinsinden
  WriteRegDWORD HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Hex Cheat" \
                   "NoModify" 1
  WriteRegDWORD HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Hex Cheat" \
                   "NoRepair" 1
                   
  Call RefreshShellIcons
SectionEnd

Section "Uninstall"
  Delete "$INSTDIR\*.*"
  RMDir /r "$INSTDIR"
  
  Delete "$SMPROGRAMS\Hex Cheat\*.*"
  RMDir "$SMPROGRAMS\Hex Cheat"
  
  Delete "$DESKTOP\Hex Cheat.lnk"
  
  DeleteRegKey HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Hex Cheat"
SectionEnd

!insertmacro MUI_FUNCTION_DESCRIPTION_BEGIN
  !insertmacro MUI_DESCRIPTION_TEXT ${SEC_DESKTOP} "Create a shortcut on the desktop"
  !insertmacro MUI_DESCRIPTION_TEXT ${SEC_STARTMENU} "Create shortcuts in the Start Menu"
!insertmacro MUI_FUNCTION_DESCRIPTION_END
