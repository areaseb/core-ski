<?php

return [
    'types' => [
        'P' => 'Proforma',
        'F' => 'Fattura',
        'R' => 'Ricevuta',
        'A' => 'Nota di Accredito',
        'D' => 'DDT',
        'U' => 'Autofattura'
    ],
    'payment_modes' => [
        ""=> "",
        "B" => "Bonifico",
        "C" => "Contanti",
        "P" => "POS",
        "S" => "SumUp",
        "A" => "Paypal",
        "Y" => "Payleven"         
    ],
    'payment_types' => [
        "BOVF" => "Bonifico vista fattura",
        "BO3P" => "Bonifico 30%",
        "BO5P" => "Bonifico 50%",
        "BOFM" => "Bonifico fine mese",
        "BO3F" => "Bonifico 30gg fine mese",
        "BO6F" => "Bonifico 60gg fine mese",
        "BO9F" => "Bonifico 90gg fine mese",
        "RIDI" => "Rimessa diretta",
        "RBFM" => "Ri.Ba. fine mese",
        "RB3F" => "Ri.Ba. 30gg fine mese",
        "RB4F" => "Ri.Ba. 40gg fine mese",
        "RB6F" => "Ri.Ba. 60gg fine mese",
        "RB9F" => "Ri.Ba. 90gg fine mese",
        "CONT" => "Contanti",
        "ASSE" => "Assegno",
        "POSS" => "POS"
    ],
    'payment_types_dead_lines' => [
        ""     => null,
        "BOVF" => 0,
        "BO3P" => 0,
        "BO5P" => 0,
        "BOFM" => 1,
        "BO3F" => 31,
        "BO6F" => 62,
        "BO9F" => 93,
        "RIDI" => 0,
        "RBFM" => 1,
        "RB3F" => 31,
        "RB4F" => 40,
        "RB6F" => 62,
        "RB9F" => 93,
        "CONT" => 0,
        "ASSE" => 0,
        "POSS" => 0
    ],
    'regime' => [
        "RF01" => "Ordinario",
        "RF02" => "Regime dei minimi (art.1, c.96-117, L. 244/07)",
        "RF04" => "Agricoltura e pesca (artt.34 e 34-bis, DPR 633/72)",
        "RF05" => "Sali e tabacchi (art.74, c.1, DPR. 633/72)",
        "RF06" => "Commercio fiammiferi (art.74, c.1, DPR 633/72)",
        "RF07" => "Editoria (art.74, c.1, DPR 633/72)",
        "RF08" => "Servizi telefonia pubblica (art.74, c.1, DPR 633/72)",
        "RF09" => "Rivendita doc trasporto pubblico e sosta (art.74, c.1, DPR 633/72)",
        "RF10" => "Intrattenimento e giochi DPR 640/72 (art.74, c.6, DPR 633/72)",
        "RF11" => "Agenzia viaggi (art.74-ter, DPR 633/72)",
        "RF12" => "Agriturismo (art.5, c.2, L. 413/91)",
        "RF13" => "Vendita a domincilio (art.25-bis, c.6, DPR 600/73)",
        "RF14" => "Rivendita beni usati, antiquariato e oggetti d'arte (art.36, DL 41/95)",
        "RF15" => "Agenzie di vendita all'asta di oggetti d'arte, collezioni o antiquariato (art.40-bis, DL 41/95)",
        "RF16" => "IVA per cassa P.A. (art.6, c.5, DPR 633/72)",
        "RF17" => "IVA per cassa (art. 32-bis, DL 83/2012)",
        "RF18" => "Altro",
        "RF19" => "Regime forfettario (art.1, c.54-89, L. 190/2014)"
    ]
];
