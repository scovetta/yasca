<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\Deprecated\BannedFunctions;

final class C extends \Yasca\Plugin {
	use Base, \Yasca\Plugins\BuiltIn\SimpleFileContentsRegex;
	protected function getRegex(){return <<<'EOT'
`(?x)
	#Comment protection
	^ (?!//)(?:.(?!//))*?	\b
	(
		#List from: http://msdn.microsoft.com/en-us/library/bb288454.aspx

		#Banned string copy functions
		strcpy|wcscpy|_tcscpy|_mbscpy|StrCpy|StrCpyA|StrCpyW|lstrcpy|lstrcpyA|
		lstrcpyW|strcpyA|strcpyW|_tccpy|_mbccpy|
		strncpy|wcsncpy|_tcsncpy|_mbsncpy|_mbsnbcpy|StrCpyN|StrCpyNA|StrCpyNW|
		StrNCpy|strcpynA|StrNCpyA|StrNCpyW|lstrcpyn|lstrcpynA|lstrcpynW|_fstrncpy|

		#Banned string concatenation functions
		strcat|wcscat|_tcscat|_mbscat|StrCat|StrCatA|StrCatW|lstrcat|lstrcatA|
		lstrcatW|StrCatBuffW|StrCatBuff|StrCatBuffA|StrCatChainW|strcatA|strcatW|_tccat|_mbccat|
		strncat|wcsncat|_tcsncat|_mbsncat|_mbsnbcat|StrCatN|StrCatNA|StrCatNW|StrNCat|StrNCatA|
		StrNCatW|lstrncat|lstrcatnA|lstrcatnW|lstrcatn|_fstrncat|

		#Banned sprintf functions
		wnsprintf|wnsprintfA|wnsprintfW|sprintfW|sprintfA|wsprintf|wsprintfW|
		wsprintfA|sprintf|swprintf|_stprintf|
		_snwprintf|_snprintf|_sntprintf|nsprintf|

		#Banned vararg sprintf functions
		wvsprintf|wvsprintfA|wvsprintfW|vsprintf|_vstprintf|vswprintf|
		_vsnprintf|_vsnwprintf|_vsntprintf|wvnsprintf|wvnsprintfA|wvnsprintfW|

		#Banned string tokenizing functions
		strtok|_tcstok|wcstok|_mbstok|

		#Banned path functions
		Makepath|_tmakepath|_makepath|_wmakepath|
		_splitpath|_tsplitpath|_wsplitpath|

		#Banned scanf functions
		scanf|wscanf|_tscanf|sscanf|swscanf|_stscanf|
		snscanf|snwscanf|_sntscanf|

		#Banned numeric conversion
		_itoa|_itow|_i64toa|_i64tow|_ui64toa|_ui64tot|_ui64tow|_ultoa|_ultot|_ultow|

		#Banned gets functions
		gets|_getts|_gettws|

		#Banned IsBad functions
		sBadWritePtr|IsBadHugeWritePtr|IsBadReadPtr|IsBadHugeReadPtr|IsBadCodePtr|IsBadStringPtr|

		#Banned OEM functions
		CharToOem|CharToOemA|CharToOemW|OemToChar|OemToCharA|OemToCharW|CharToOemBuffA|CharToOemBuffW|

		#Banned stack dynamic memory alloc functions
		alloca|_alloca|

		#Banned string length functions
		strlen|wcslen|_mbslen|_mbstrlen|StrLen|lstrlen
	)
	[\b\Z]
`u
EOT;
	}
}