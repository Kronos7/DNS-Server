<?
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include "Server.php";

	$host = 'localhost';
	$username = 'root';
	$password = '1234';
	$db = 'DNS';

	mysql_connect( $host, $username, $password ); // Veritabani baglantisi
	mysql_select_db( $db );

	$q = mysql_query('SELECT * FROM My_Records'); // 'My_Records' tablosundaki kayitlar bir degiskene aktariliyor.

	while( $row = mysql_fetch_assoc($q) ) // 'My_Records' tablosundaki kayitlar karsilastirma yapabilmek icin bicimlendiriliyor.
	{
	 $ips[$row['domain']]['A'] = $row['ip'];
	}

	foreach($ips as $domain => $ip) // Basinda 'www' olan ve olmayan ayni alan adlarinin ayni adresi döndürmesi icin degisken bicimlendiriliyor
	{
 	 $ips['www.'.$domain]['A'] = $ip['A'];
	 //var_dump( $ips );
	}

function dnshandler($q_domain,$type) // Sorgusu yapilan alan adinin bir adresle iliskilendirildigi kisim
{
	global $ips;

	if ( isset($ips[$q_domain][$type]) ) // Alan adinin 'My_Records' tablosunda olup olmadigina bakiliyor
	{
	 return $ips[$q_domain][$type];
	}
	
	else // Alan adi 'My_Records' tablosunda yoksa 'Updated_Records' tablosuna bakiliyor 
	{

	 $host = 'localhost';
	 $username = 'root';
	 $password = '1234';
	 $db = 'DNS';

	 mysql_connect( $host, $username, $password );
	 mysql_select_db( $db );

	 $q = mysql_query('SELECT * FROM Updated_Records WHERE domain="'.$q_domain.'"'); // 'Updated_Records' tablosundaki kayitlar bir degiskene aktariliyor.
	 
		if( mysql_num_rows($q) == 0 ) // Alan adinin 'Updated_Records' tablosunda olup olmadigina bakiliyor
		{
		 $result = gethostbyname( $q_domain ); // Bu fonksiyon internet üzerinden bir IP adresi döndürüyor

		 mysql_query('INSERT INTO Updated_Records (domain,ip)
			      VALUES("'.$q_domain.'","'.$result.'")'); // Yeni adres, 'Updated_Records' tablosuna ekleniyor

		 return $result;
		}

		else
		{
	 	 $result = mysql_fetch_assoc( $q );
	 	 return $result['ip']; 
		}

	}
}

$dns = new Server( "dnshandler" );

?>
