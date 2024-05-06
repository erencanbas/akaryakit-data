
    

<?php

function getProvinceNameFromPlateCode($plateCode)
{
    $plateCodes = array(
        '01' => 'Adana',
        '02' => 'Adıyaman',
        '03' => 'Afyonkarahisar',
        '04' => 'Ağrı',
        '05' => 'Amasya',
        '06' => 'Ankara',
        '07' => 'Antalya',
        '08' => 'Artvin',
        '09' => 'Aydın',
        '10' => 'Balıkesir',
        '11' => 'Bilecik',
        '12' => 'Bingöl',
        '13' => 'Bitlis',
        '14' => 'Bolu',
        '15' => 'Burdur',
        '16' => 'Bursa',
        '17' => 'Çanakkale',
        '18' => 'Çankırı',
        '19' => 'Çorum',
        '20' => 'Denizli',
        '21' => 'Diyarbakır',
        '22' => 'Edirne',
        '23' => 'Elazığ',
        '24' => 'Erzincan',
        '25' => 'Erzurum',
        '26' => 'Eskişehir',
        '27' => 'Gaziantep',
        '28' => 'Giresun',
        '29' => 'Gümüşhane',
        '30' => 'Hakkari',
        '31' => 'Hatay',
        '32' => 'Isparta',
        '33' => 'Mersin',
        '34' => 'İstanbul',
        '35' => 'İzmir',
        '36' => 'Kars',
        '37' => 'Kastamonu',
        '38' => 'Kayseri',
        '39' => 'Kırklareli',
        '40' => 'Kırşehir',
        '41' => 'Kocaeli',
        '42' => 'Konya',
        '43' => 'Kütahya',
        '44' => 'Malatya',
        '45' => 'Manisa',
        '46' => 'Kahramanmaraş',
        '47' => 'Mardin',
        '48' => 'Muğla',
        '49' => 'Muş',
        '50' => 'Nevşehir',
        '51' => 'Niğde',
        '52' => 'Ordu',
        '53' => 'Rize',
        '54' => 'Sakarya',
        '55' => 'Samsun',
        '56' => 'Siirt',
        '57' => 'Sinop',
        '58' => 'Sivas',
        '59' => 'Tekirdağ',
        '60' => 'Tokat',
        '61' => 'Trabzon',
        '62' => 'Tunceli',
        '63' => 'Şanlıurfa',
        '64' => 'Uşak',
        '65' => 'Van',
        '66' => 'Yozgat',
        '67' => 'Zonguldak',
        '68' => 'Aksaray',
        '69' => 'Bayburt',
        '70' => 'Karaman',
        '71' => 'Kırıkkale',
        '72' => 'Batman',
        '73' => 'Şırnak',
        '74' => 'Bartın',
        '75' => 'Ardahan',
        '76' => 'Iğdır',
        '77' => 'Yalova',
        '78' => 'Karabük',
        '79' => 'Kilis',
        '80' => 'Osmaniye',
        '81' => 'Düzce',
    );
    return isset($plateCodes[$plateCode]) ? $plateCodes[$plateCode] : null;
}

function convertToEnglishChars($str)
{
    $turkishChars = array(
        'ğ' => 'g',
        'ü' => 'u',
        'ş' => 's',
        'ı' => 'i',
        'ö' => 'o',
        'ç' => 'c',
        'Ğ' => 'G',
        'Ü' => 'U',
        'Ş' => 'S',
        'İ' => 'I',
        'Ö' => 'O',
        'Ç' => 'C'
    );

    $convertedStr = strtr($str, $turkishChars);
    return strtolower($convertedStr);
}

if (isset($_POST["getFuelPrice"])) {
    $data = array();
    $province = convertToEnglishChars(getProvinceNameFromPlateCode($_POST["province_name"]));
    if (!isset($_POST["district_name"]) || $_POST["district_name"] == "0") {
        $url = "https://mazot-fiyatlari.com/fiyatlar/$province";
    } else {
        $lowercase = convertToEnglishChars($_POST["district_name"]);
        $url = "https://mazot-fiyatlari.com/fiyatlar/$province/$lowercase";
    }

    error_reporting(0);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($ch);

    if ($output === false) {
        echo "Error: " . curl_error($ch);
    } else {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // Hata mesajlarını görmezden gel
        $dom->loadHTML($output);
        libxml_clear_errors();

        // Tablo elemanını al
        $tables = $dom->getElementsByTagName('table');
        
        foreach ($tables as $table) {
            $rows = $table->getElementsByTagName('tr');
            
            foreach ($rows as $row) {
                $cells = $row->getElementsByTagName('td');
                // Firma adı
                $firma = $cells->item(0)->nodeValue;
                // Firma adı boş değilse
                if (!empty($firma)) {
                    $data[] = array(
                        "firma" => $firma,
                        "kursunsuz_benzin" => trim($cells->item(1)->nodeValue),
                        "kursunsuz_benzin_diger" => trim($cells->item(2)->nodeValue),
                        "motorin" => trim($cells->item(3)->nodeValue),
                        "motorin_diger" => trim($cells->item(4)->nodeValue),
                        "lpg" => trim($cells->item(5)->nodeValue)
                    );
                }
            }
        }
    }

    curl_close($ch);

    echo json_encode($data);
}
