<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_get_cities_dataset()
{
    static $cities = null;
    if ($cities !== null) {
        return $cities;
    }

    $cityNames = [
        "Warszawa", "Kraków", "Wrocław", "Łódź", "Poznań", "Gdańsk", "Szczecin", "Bydgoszcz", "Lublin", "Białystok",
        "Katowice", "Gdynia", "Częstochowa", "Radom", "Sosnowiec", "Toruń", "Kielce", "Rzeszów", "Gliwice", "Zabrze",
        "Olsztyn", "Bielsko-Biała", "Bytom", "Zielona Góra", "Rybnik", "Ruda Śląska", "Tychy", "Dąbrowa Górnicza", "Opole", "Elbląg",
        "Płock", "Wałbrzych", "Włocławek", "Gorzów Wielkopolski", "Tarnów", "Chorzów", "Koszalin", "Kalisz", "Legnica", "Grudziądz",
        "Słupsk", "Jaworzno", "Jastrzębie-Zdrój", "Nowy Sącz", "Jelenia Góra", "Siedlce", "Konin", "Piła", "Ostrowiec Świętokrzyski", "Inowrocław",
        "Lubin", "Ostrołęka", "Stargard", "Gniezno", "Suwałki", "Pabianice", "Chełm", "Przemyśl", "Zamość", "Tomaszów Mazowiecki",
        "Łomża", "Leszno", "Stalowa Wola", "Puławy", "Tarnowskie Góry", "Będzin", "Zgierz", "Biała Podlaska", "Ełk", "Pruszków",
        "Nowy Targ", "Piekary Śląskie", "Racibórz", "Mielec", "Świdnica", "Siemianowice Śląskie", "Tczew", "Piotrków Trybunalski", "Bełchatów", "Starachowice",
        "Bolesławiec", "Wejherowo", "Skierniewice", "Ostrów Wielkopolski", "Świnoujście", "Kędzierzyn-Koźle", "Sopot", "Zawiercie", "Knurów", "Rumia",
        "Przemków", "Żory", "Wodzisław Śląski", "Marki", "Piaseczno", "Mysłowice", "Sanok", "Ciechanów", "Reda", "Radzionków",
        "Radzyń Podlaski", "Nysa", "Jarosław", "Łęczna", "Pszczyna", "Cieszyn", "Mikołów", "Śrem", "Kutno", "Wyszków",
        "Iława", "Augustów", "Brzeg", "Namysłów", "Krosno", "Krapkowice", "Mława", "Olecko", "Bochnia", "Andrychów",
        "Wadowice", "Kościan", "Września", "Szamotuły", "Jarocin", "Wągrowiec", "Sieradz", "Zduńska Wola", "Głogów", "Lubartów",
        "Polkowice", "Brodnica", "Bielawa", "Lubań", "Łeba", "Szczytno", "Lidzbark Warmiński", "Mrągowo", "Pisz", "Kętrzyn",
        "Bartoszyce", "Ostróda", "Nidzica", "Kołobrzeg", "Białogard", "Drawsko Pomorskie", "Gryfino", "Police", "Goleniów", "Kamień Pomorski",
        "Myślenice", "Limanowa", "Gorlice", "Brzesko", "Nowy Dwór Mazowiecki", "Legionowo", "Mińsk Mazowiecki", "Wołomin", "Otwock", "Sochaczew",
        "Grodzisk Mazowiecki", "Żyrardów", "Pułtusk", "Sandomierz", "Busko-Zdrój", "Skarżysko-Kamienna", "Końskie", "Biłgoraj", "Hrubieszów", "Dębica",
        "Jasło", "Krosno Odrzańskie", "Żary", "Nowa Sól", "Świebodzin", "Międzyrzecz", "Sulechów", "Łuków", "Turek", "Koło",
        "Złotów", "Chojnice", "Starogard Gdański", "Kwidzyn", "Malbork", "Prudnik", "Kluczbork", "Szczecinek", "Wałcz", "Bytów",
        "Człuchów", "Puck", "Lębork", "Sierpc", "Gostynin", "Płońsk", "Częstochowa Północ", "Myszków", "Radzymin", "Wieluń",
    ];

    $voivodeships = [
        "warszawa" => "mazowieckie", "radom" => "mazowieckie", "siedlce" => "mazowieckie", "plock" => "mazowieckie",
        "ostroleka" => "mazowieckie", "pruszkow" => "mazowieckie", "marki" => "mazowieckie", "piaseczno" => "mazowieckie",
        "ciechanow" => "mazowieckie", "nowy-dwor-mazowiecki" => "mazowieckie", "legionowo" => "mazowieckie", "minsk-mazowiecki" => "mazowieckie",
        "wolomin" => "mazowieckie", "otwock" => "mazowieckie", "sochaczew" => "mazowieckie", "grodzisk-mazowiecki" => "mazowieckie",
        "zyrardow" => "mazowieckie", "pultusk" => "mazowieckie", "sierpc" => "mazowieckie", "gostynin" => "mazowieckie",
        "plonsk" => "mazowieckie", "radzymin" => "mazowieckie",

        "krakow" => "malopolskie", "tarnow" => "malopolskie", "nowy-sacz" => "malopolskie", "nowy-targ" => "malopolskie",
        "myslenice" => "malopolskie", "limanowa" => "malopolskie", "gorlice" => "malopolskie", "brzesko" => "malopolskie",
        "bochnia" => "malopolskie", "andrychow" => "malopolskie", "wadowice" => "malopolskie",

        "wroclaw" => "dolnoslaskie", "walbrzych" => "dolnoslaskie", "legnica" => "dolnoslaskie", "jelenia-gora" => "dolnoslaskie",
        "swidnica" => "dolnoslaskie", "boleslawiec" => "dolnoslaskie", "lubin" => "dolnoslaskie", "glogow" => "dolnoslaskie",
        "polkowice" => "dolnoslaskie", "bielawa" => "dolnoslaskie", "luban" => "dolnoslaskie",

        "lodz" => "lodzkie", "tomaszow-mazowiecki" => "lodzkie", "zgierz" => "lodzkie", "pabianice" => "lodzkie",
        "piotrkow-trybunalski" => "lodzkie", "belchatow" => "lodzkie", "skierniewice" => "lodzkie", "sieradz" => "lodzkie",
        "zdunska-wola" => "lodzkie", "kutno" => "lodzkie", "wielun" => "lodzkie",

        "poznan" => "wielkopolskie", "kalisz" => "wielkopolskie", "konin" => "wielkopolskie", "leszno" => "wielkopolskie",
        "gniezno" => "wielkopolskie", "pila" => "wielkopolskie", "ostrow-wielkopolski" => "wielkopolskie", "koscian" => "wielkopolskie",
        "wrzesnia" => "wielkopolskie", "szamotuly" => "wielkopolskie", "jarocin" => "wielkopolskie", "wagrowiec" => "wielkopolskie",
        "srem" => "wielkopolskie", "turek" => "wielkopolskie", "kolo" => "wielkopolskie", "zlotow" => "wielkopolskie",

        "gdansk" => "pomorskie", "gdynia" => "pomorskie", "slupsk" => "pomorskie", "sopot" => "pomorskie",
        "tczew" => "pomorskie", "wejherowo" => "pomorskie", "rumia" => "pomorskie", "reda" => "pomorskie",
        "leba" => "pomorskie", "chojnice" => "pomorskie", "starogard-gdanski" => "pomorskie", "kwidzyn" => "pomorskie",
        "malbork" => "pomorskie", "bytow" => "pomorskie", "czluchow" => "pomorskie", "puck" => "pomorskie", "lebork" => "pomorskie",

        "szczecin" => "zachodniopomorskie", "koszalin" => "zachodniopomorskie", "stargard" => "zachodniopomorskie", "swinoujscie" => "zachodniopomorskie",
        "kolobrzeg" => "zachodniopomorskie", "bialogard" => "zachodniopomorskie", "drawsko-pomorskie" => "zachodniopomorskie",
        "gryfino" => "zachodniopomorskie", "police" => "zachodniopomorskie", "goleniow" => "zachodniopomorskie", "kamien-pomorski" => "zachodniopomorskie",
        "szczecinek" => "zachodniopomorskie", "walcz" => "zachodniopomorskie",

        "bydgoszcz" => "kujawsko-pomorskie", "torun" => "kujawsko-pomorskie", "grudziadz" => "kujawsko-pomorskie", "inowroclaw" => "kujawsko-pomorskie",
        "wloclawek" => "kujawsko-pomorskie", "brodnica" => "kujawsko-pomorskie",

        "lublin" => "lubelskie", "zamosc" => "lubelskie", "chelm" => "lubelskie", "biala-podlaska" => "lubelskie",
        "pulawy" => "lubelskie", "hrubieszow" => "lubelskie", "bilgoraj" => "lubelskie", "lukow" => "lubelskie",
        "lubartow" => "lubelskie", "radzyn-podlaski" => "lubelskie",

        "bialystok" => "podlaskie", "lomza" => "podlaskie", "suwalki" => "podlaskie", "augustow" => "podlaskie",

        "katowice" => "slaskie", "sosnowiec" => "slaskie", "gliwice" => "slaskie", "zabrze" => "slaskie",
        "czestochowa" => "slaskie", "bytom" => "slaskie", "rybnik" => "slaskie", "ruda-slaska" => "slaskie",
        "tychy" => "slaskie", "dabrowa-gornicza" => "slaskie", "chorzow" => "slaskie", "jaworzno" => "slaskie",
        "jastrzebie-zdroj" => "slaskie", "tarnowskie-gory" => "slaskie", "bedzin" => "slaskie", "piekary-slaskie" => "slaskie",
        "raciborz" => "slaskie", "siemianowice-slaskie" => "slaskie", "zawiercie" => "slaskie", "knurow" => "slaskie",
        "zory" => "slaskie", "wodzislaw-slaski" => "slaskie", "radzionkow" => "slaskie", "pszczyna" => "slaskie",
        "cieszyn" => "slaskie", "mikolow" => "slaskie", "myslowice" => "slaskie", "czestochowa-polnoc" => "slaskie", "myszkow" => "slaskie",
        "bielsko-biala" => "slaskie",

        "opole" => "opolskie", "nysa" => "opolskie", "prudnik" => "opolskie", "kluczbork" => "opolskie",
        "brzeg" => "opolskie", "namyslow" => "opolskie", "krapkowice" => "opolskie", "kedzierzyn-kozle" => "opolskie",

        "kielce" => "swietokrzyskie", "ostrowiec-swietokrzyski" => "swietokrzyskie", "starachowice" => "swietokrzyskie", "sandomierz" => "swietokrzyskie",
        "busko-zdroj" => "swietokrzyskie", "skarzysko-kamienna" => "swietokrzyskie", "konskie" => "swietokrzyskie",

        "rzeszow" => "podkarpackie", "przemysl" => "podkarpackie", "mielec" => "podkarpackie", "stalowa-wola" => "podkarpackie",
        "krosno" => "podkarpackie", "sanok" => "podkarpackie", "debica" => "podkarpackie", "jaslo" => "podkarpackie", "jaroslaw" => "podkarpackie",

        "zielona-gora" => "lubuskie", "gorzow-wielkopolski" => "lubuskie", "zary" => "lubuskie", "nowa-sol" => "lubuskie",
        "swiebodzin" => "lubuskie", "miedzyrzecz" => "lubuskie", "sulechow" => "lubuskie", "krosno-odrzanskie" => "lubuskie",

        "olsztyn" => "warminsko-mazurskie", "elblag" => "warminsko-mazurskie", "ostroda" => "warminsko-mazurskie", "mragowo" => "warminsko-mazurskie",
        "elk" => "warminsko-mazurskie", "ilawa" => "warminsko-mazurskie", "olecko" => "warminsko-mazurskie",
        "szczytno" => "warminsko-mazurskie", "lidzbark-warminski" => "warminsko-mazurskie", "pisz" => "warminsko-mazurskie",
        "ketrzyn" => "warminsko-mazurskie", "bartoszyce" => "warminsko-mazurskie", "nidzica" => "warminsko-mazurskie",
    ];

    $marketAngles = [
        "firmy produkcyjne i B2B",
        "lokalne usługi premium",
        "e-commerce i sklepy specjalistyczne",
        "firmy technologiczne i SaaS",
        "firmy handlowe i dystrybucja",
        "usługi medyczne i beauty",
        "nieruchomości i deweloperzy",
        "edukacja i szkolenia",
    ];

    $serviceFocus = [
        "Meta Ads + landing page",
        "Google Ads + strona firmowa",
        "Meta Ads + Google Ads + CRO",
        "strona WWW + automatyzacje leadów",
        "kampanie pozyskujące klientów + analityka",
        "skalowanie ecommerce + performance",
    ];

    $localChallenges = [
        "duża konkurencja cenowa",
        "niewystarczająca widoczność oferty",
        "niska jakość leadów z kampanii",
        "słaba konwersja na stronie",
        "rozproszona komunikacja marketingowa",
        "brak spójnego lejka sprzedaży",
        "niewykorzystany ruch remarketingowy",
        "trudność w skalowaniu kosztu pozyskania",
    ];

    $localAdvantages = [
        "silny rynek lokalnych usług",
        "duża liczba firm B2B",
        "wysoka aktywność zakupowa online",
        "dostęp do specjalistów i partnerów",
        "rosnąca liczba zapytań z wyszukiwarki",
        "potencjał do sprzedaży premium",
        "stabilny popyt na usługi specjalistyczne",
        "dobry punkt startu do skalowania ogólnopolskiego",
    ];

    $seasonalityAngles = [
        "mocne Q1 i Q4",
        "wzrost popytu przed sezonem letnim",
        "większa aktywność klientów po wakacjach",
        "stabilny popyt przez cały rok",
        "duże znaczenie kampanii okresowych",
        "silna sezonowość e-commerce",
        "wzrost leadów w okresach promocyjnych",
        "przesunięcia budżetów między kanałami w ciągu roku",
    ];

    $cities = [];
    foreach ($cityNames as $index => $name) {
        $slug = sanitize_title($name);
        $cities[] = [
            "name" => $name,
            "slug" => $slug,
            "voivodeship" => $voivodeships[$slug] ?? "polska",
            "market_angle" => $marketAngles[$index % count($marketAngles)],
            "service_focus" => $serviceFocus[$index % count($serviceFocus)],
            "local_challenge" => $localChallenges[$index % count($localChallenges)],
            "local_advantage" => $localAdvantages[$index % count($localAdvantages)],
            "seasonality_angle" => $seasonalityAngles[$index % count($seasonalityAngles)],
        ];
    }

    return $cities;
}

function upsellio_get_city_by_slug($slug)
{
    foreach (upsellio_get_cities_dataset() as $city) {
        if ($city["slug"] === $slug) {
            return $city;
        }
    }

    return null;
}

