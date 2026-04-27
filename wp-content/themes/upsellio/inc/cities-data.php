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
        "krakow" => "malopolskie", "tarnow" => "malopolskie", "nowy-sacz" => "malopolskie", "nowy-targ" => "malopolskie",
        "wroclaw" => "dolnoslaskie", "walbrzych" => "dolnoslaskie", "legnica" => "dolnoslaskie", "jelenia-gora" => "dolnoslaskie",
        "lodz" => "lodzkie", "tomaszow-mazowiecki" => "lodzkie", "zgierz" => "lodzkie", "pabianice" => "lodzkie",
        "poznan" => "wielkopolskie", "kalisz" => "wielkopolskie", "konin" => "wielkopolskie", "leszno" => "wielkopolskie",
        "gdansk" => "pomorskie", "gdynia" => "pomorskie", "slupsk" => "pomorskie", "sopot" => "pomorskie",
        "szczecin" => "zachodniopomorskie", "koszalin" => "zachodniopomorskie", "stargard" => "zachodniopomorskie", "swinoujscie" => "zachodniopomorskie",
        "bydgoszcz" => "kujawsko-pomorskie", "torun" => "kujawsko-pomorskie", "grudziadz" => "kujawsko-pomorskie", "inowroclaw" => "kujawsko-pomorskie",
        "lublin" => "lubelskie", "zamosc" => "lubelskie", "chelm" => "lubelskie", "biala-podlaska" => "lubelskie",
        "bialystok" => "podlaskie", "lomza" => "podlaskie", "suwalki" => "podlaskie", "elk" => "warminsko-mazurskie",
        "katowice" => "slaskie", "sosnowiec" => "slaskie", "gliwice" => "slaskie", "zabrze" => "slaskie",
        "opole" => "opolskie", "nysa" => "opolskie", "prudnik" => "opolskie", "kluczbork" => "opolskie",
        "kielce" => "swietokrzyskie", "ostrowiec-swietokrzyski" => "swietokrzyskie", "starachowice" => "swietokrzyskie", "sandomierz" => "swietokrzyskie",
        "rzeszow" => "podkarpackie", "przemysl" => "podkarpackie", "mielec" => "podkarpackie", "stalowa-wola" => "podkarpackie",
        "zielona-gora" => "lubuskie", "gorzow-wielkopolski" => "lubuskie", "zary" => "lubuskie", "nowa-sol" => "lubuskie",
        "olsztyn" => "warminsko-mazurskie", "elblag" => "warminsko-mazurskie", "ostroda" => "warminsko-mazurskie", "mragowo" => "warminsko-mazurskie",
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

