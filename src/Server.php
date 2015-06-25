<?
class Server
{
    var $func;
    var $socket;
    var $types;
    var $localip;

    function Server( $func_dh ) // Arguman olarak 'Start' kismindaki "dns_handler" fonksiyonunun ismini aliyor
    {
	$ip = NULL;
        $this->localip = $ip;
        $this->func = $func_dh;
        $this->types = array(
            "A" => 1,
            "NS" => 2,
            "CNAME" => 5,
            "SOA" => 6,
            "WKS" => 11,
            "PTR" => 12,
            "HINFO" => 13,
            "MX" => 15,
            "TXT" => 16,
            "RP" => 17,
            "SIG" => 24,
            "KEY" => 25,
            "LOC" => 29,
            "NXT" => 30,
            "AAAA" => 28,
            "CERT" => 37,
            "A6" => 38,
            "AXFR" => 252,
            "IXFR" => 251,
            "*" => 255
        );
 
        $this->Begin();
    }
    
    function Begin()
    {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); // Soket yaratilan kisim
        if ($this->socket < 0)
        {
            printf("Error in line %d", __LINE__ - 3);
            exit();
        }
        if (socket_bind($this->socket, "127.0.0.2", "53") == false) // Sunucunun calisacagi IP adresi ve port numarasinin belirlendigi kisim
        {
            printf("Error in line %d",__LINE__ - 2);
            exit();
        }

        while(1)
        {

            $len = socket_recvfrom($this->socket, $buf, 1024*4, 0, $ip, $port); // Herhangi bir istemciden gelen sorgunun ve paketin yakalandigi kisim

            if ($len > 0)
            {
                $this->HandleQuery( $buf, $ip, $port );        
            }    
        }
    }
    
    function HandleQuery($buf, $clientip, $clientport) // Bu fonksiyonda istemciden gelen paket aciliyor; IP adresinin yer aldigi paket olusturuluyor ve gonderiliyor.
    {
    
        $q_domain = "";

        $temp = substr( $buf, 12 ); // Gelen paketteki alan adini belirlemek icin anlamsiz kisim atiliyor

        $k = strlen( $temp );

        for( $i = 0; $i < $k; $i++ )
        {
            $len = ord( $temp[$i] ); // String, ASCII bicimine ceviriliyor

            if ( $len == 0 )
                break;

            $q_domain .= substr($temp, $i+1, $len) . ".";

            $i += $len;
        }

        $i++; $i++;
        
        $querytype = array_search( (string)ord($temp[$i]), $this->types ) ; // Sorgusu yapilan alan adi tipi belirleniyor

        $q_domain = substr($q_domain, 0, strlen( $q_domain ) - 1);
        $func_dh = $this->func;
        $ips = $func_dh($q_domain, $querytype); // "dns_handler" fonksiyonu kullaniliyor

	// Gidecek paketin bicimlendirilmesi...
        $answ = $buf[0].$buf[1].chr(129).chr(128).$buf[4].$buf[5].$buf[4].$buf[5].chr(0).chr(0).chr(0).chr(0);
        $answ .= $temp;
        $answ .= chr(192).chr(12);
        $answ .= chr(0).chr(1).chr(0).chr(1).chr(0).chr(0).chr(0).chr(60).chr(0).chr(4);
        $answ .= $this->TransformIP($ips); // IP adresinin, gidecek pakete dahil edildigi kisim
             

        if ( socket_sendto($this->socket, $answ, strlen($answ), 0,$clientip, $clientport) === false ) // Gelen IP adresi ve port numarasina yanit iceren paketin gonderilmesi
            printf("Error in socket\n");     
    }
    
    function TransformIP($ip) // IP adresini, pakete uygun karekter bicimine dönüstüren fonksiyon
    {
        $nip = "";
	//echo $ip . "\n";
        foreach( explode( ".", $ip ) as $pip )
            $nip .= chr( $pip );
	//echo $nip . "\n";
        return $nip;
    }
}


?>
