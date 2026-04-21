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
        "Warszawa", "Krakow", "Wroclaw", "Lodz", "Poznan", "Gdansk", "Szczecin", "Bydgoszcz", "Lublin", "Bialystok",
        "Katowice", "Gdynia", "Czestochowa", "Radom", "Sosnowiec", "Torun", "Kielce", "Rzeszow", "Gliwice", "Zabrze",
        "Olsztyn", "Bielsko-Biala", "Bytom", "Zielona Gora", "Rybnik", "Ruda Slaska", "Tychy", "Dabrowa Gornicza", "Opole", "Elblag",
        "Plock", "Walbrzych", "Wloclawek", "Gorzow Wielkopolski", "Tarnow", "Chorzow", "Koszalin", "Kalisz", "Legnica", "Grudziadz",
        "Slupsk", "Jaworzno", "Jastrzebie-Zdroj", "Nowy Sacz", "Jelenia Gora", "Siedlce", "Konin", "Pila", "Ostrowiec Swietokrzyski", "Inowroclaw",
        "Lubin", "Ostroleka", "Stargard", "Gniezno", "Suwalki", "Pabianice", "Chelm", "Przemysl", "Zamosc", "Tomaszow Mazowiecki",
        "Lomza", "Leszno", "Stalowa Wola", "Pulawy", "Tarnowskie Gory", "Bedzin", "Zgierz", "Biala Podlaska", "Ełk", "Pruszkow",
        "Nowy Targ", "Piekary Slaskie", "Raciborz", "Mielec", "Swidnica", "Siemianowice Slaskie", "Tczew", "Piotrkow Trybunalski", "Belchatow", "Starachowice",
        "Boleslawiec", "Wejherowo", "Skierniewice", "Ostrow Wielkopolski", "Swinoujscie", "Kedzierzyn-Kozle", "Sopot", "Zawiercie", "Knurow", "Rumia",
        "Przemkow", "Zory", "Wodzislaw Slaski", "Marki", "Piaseczno", "Myslowice", "Sanok", "Ciechanow", "Reda", "Radzionkow",
        "Radzyn Podlaski", "Nysa", "Jaroslaw", "Leczna", "Pszczyna", "Cieszyn", "Mikolow", "Srem", "Kutno", "Wyszkow",
        "Ilawa", "Augustow", "Brzeg", "Namyslow", "Krosno", "Krapkowice", "Mlawa", "Olecko", "Bochnia", "Andrychow",
        "Wadowice", "Koscian", "Wrzesnia", "Szamotuly", "Jarocin", "Wagrowiec", "Sieradz", "Zdunska Wola", "Glogow", "Lubartow",
        "Polkowice", "Brodnica", "Bielawa", "Luban", "Leba", "Szczytno", "Lidzbark Warminski", "Mragowo", "Pisz", "Ketrzyn",
        "Bartoszyce", "Ostroda", "Nidzica", "Kolobrzeg", "Bialogard", "Drawsko Pomorskie", "Gryfino", "Police", "Goleniow", "Kamien Pomorski",
        "Myslenice", "Limanowa", "Gorlice", "Brzesko", "Nowy Dwor Mazowiecki", "Legionowo", "Mińsk Mazowiecki", "Wolomin", "Otwock", "Sochaczew",
        "Grodzisk Mazowiecki", "Zyrardow", "Pultusk", "Sandomierz", "Busko-Zdroj", "Skarzysko-Kamienna", "Konskie", "Bilgoraj", "Hrubieszow", "Debica",
        "Jaslo", "Krosno Odrzanskie", "Zary", "Nowa Sol", "Swiebodzin", "Miedzyrzecz", "Sulechow", "Lukow", "Turek", "Kolo",
        "Zlotow", "Chojnice", "Starogard Gdanski", "Kwidzyn", "Malbork", "Prudnik", "Kluczbork", "Szczecinek", "Walcz", "Bytow",
        "Czluchow", "Puck", "Lębork", "Sierpc", "Gostynin", "Plonsk", "Czestochowa Polnoc", "Myszkow", "Radzymia", "Wielun",
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
        "lokalne uslugi premium",
        "e-commerce i sklepy specjalistyczne",
        "firmy technologiczne i SaaS",
        "firmy handlowe i dystrybucja",
        "uslugi medyczne i beauty",
        "nieruchomosci i deweloperzy",
        "edukacja i szkolenia",
    ];

    $serviceFocus = [
        "Meta Ads + landing page",
        "Google Ads + strona firmowa",
        "Meta Ads + Google Ads + CRO",
        "strona www + automatyzacje leadow",
        "kampanie lead generation + analityka",
        "skalowanie ecommerce + performance",
    ];

    $localChallenges = [
        "duza konkurencja cenowa",
        "niewystarczajaca widocznosc oferty",
        "niska jakosc leadow z kampanii",
        "slaba konwersja na stronie",
        "rozproszona komunikacja marketingowa",
        "brak spojnego lejka sprzedazy",
        "niewykorzystany ruch remarketingowy",
        "trudnosc w skalowaniu kosztu pozyskania",
    ];

    $localAdvantages = [
        "silny rynek lokalnych uslug",
        "duza liczba firm B2B",
        "wysoka aktywnosc zakupowa online",
        "dostep do specjalistow i partnerow",
        "rosnaca liczba zapytan z wyszukiwarki",
        "potencjal do sprzedazy premium",
        "stabilny popyt na uslugi specjalistyczne",
        "dobry punkt startu do skalowania ogolnopolskiego",
    ];

    $seasonalityAngles = [
        "mocne Q1 i Q4",
        "wzrost popytu przed sezonem letnim",
        "wieksza aktywnosc klientow po wakacjach",
        "stabilny popyt przez caly rok",
        "duze znaczenie kampanii okresowych",
        "silna sezonowosc e-commerce",
        "wzrost leadow w okresach promocyjnych",
        "przesuniecia budzetow miedzy kanalami w ciagu roku",
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

