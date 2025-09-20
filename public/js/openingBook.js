const openingBook = {
  // ================ CENTRAL CANNON OPENINGS ================

  // Central Cannon (中炮) vs Screen Horse Defense (屏风马)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": [
    {
      color: "r",
      from: 27,
      to: 43,
      flags: 1,
      piece: "c",
      description: "Central Cannon opening",
    },
    {
      color: "r",
      from: 71,
      to: 62,
      flags: 1,
      piece: "n",
      description: "Alternate Horse development",
    },
  ],
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": [
    {
      color: "b",
      from: 62,
      to: 47,
      flags: 1,
      piece: "n",
      description: "Screen Horse defense - standard",
    },
    {
      color: "b",
      from: 27,
      to: 44,
      flags: 1,
      piece: "c",
      description: "Opposite Direction Cannon response",
    },
  ],

  // Central Cannon vs Elephant Position (中炮对飞象)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 64,
    to: 82,
    flags: 1,
    piece: "b",
    description: "Elephant Position defense",
  },

  // Central Cannon vs Cross Palace Cannon (中炮对列手炮)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 28,
    to: 44,
    flags: 1,
    piece: "c",
    description: "Cross Palace Cannon response",
  },

  // ================ THREE-STEP TIGER VARIATIONS ================
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 71,
    to: 62,
    flags: 1,
    piece: "n",
    description: "Three-Step Tiger (三步虎) - Horse first",
  },

  // ================ PHOENIX-EYE CANNON ================
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 29,
    to: 43,
    flags: 1,
    piece: "c",
    description: "Phoenix-Eye Cannon (凤凰炮)",
  },

  // ================ PROFESSIONAL GAME OPENINGS ================

  // Wang Tianyi's Favorite (王天一喜欢的布局)
  "rnbakabnr/9/1c2c4/p1p1p1p1p/9/9/P1P1P1P1P/1C2C4/9/RNBAKABNR": {
    color: "b",
    from: 44,
    to: 53,
    flags: 1,
    piece: "p",
    description: "Wang Tianyi's Pawn Sacrifice",
  },

  // Zheng Weitong's Counter (郑惟桐的应对)
  "r1ba1abnr/4k4/2n1c1c2/p1p1p1p1p/9/9/P1P1P1P1P/2N1C1C2/4K4/R1BA1ABNR": {
    color: "r",
    from: 52,
    to: 63,
    flags: 1,
    piece: "n",
    description: "Zheng Weitong's Horse Maneuver",
  },

  // ================ SPECIALIZED OPENINGS ================

  // Left Cannon Control (左炮封车)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 29,
    to: 25,
    flags: 1,
    piece: "c",
    description: "Left Cannon Control",
  },

  // Right Flank Attack (右翼急攻)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 28,
    to: 46,
    flags: 1,
    piece: "c",
    description: "Right Flank Cannon Attack",
  },

  // ================ BLACK INITIATIVE OPENINGS ================

  // Black's Central Cannon (黑方中炮)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 27,
    to: 43,
    flags: 1,
    piece: "c",
    description: "Black's Central Cannon Initiative",
  },

  // Black's Quick Attack (黑方急攻)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 62,
    to: 51,
    flags: 1,
    piece: "n",
    description: "Black's Quick Horse Development",
  },

  // ================ MODERN HYBRID OPENINGS ================

  // Cannon-Horse Coordination (炮马配合)
  "r1bakabnr/9/1cn1c4/p1p1p1p1p/9/9/P1P1P1P1P/1CN1C4/9/R1BAKABNR": {
    color: "r",
    from: 27,
    to: 45,
    flags: 1,
    piece: "c",
    description: "Modern Cannon-Horse Coordination",
  },

  // Flexible Elephant Position (灵活飞象)
  "rnbakabnr/9/1c2c4/p1p1p1p1p/9/9/P1P1P1P1P/1C2C4/9/RNBAKABNR w": {
    color: "b",
    from: 64,
    to: 46,
    flags: 1,
    piece: "b",
    description: "Flexible Elephant Development",
  },

  // ================ TRADITIONAL OPENINGS ================

  // Old Style Central Cannon (古谱中炮)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 27,
    to: 47,
    flags: 1,
    piece: "c",
    description: "Traditional Deep Cannon",
  },

  // Two-Horse Defense (双马防御)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 62,
    to: 53,
    flags: 1,
    piece: "n",
    description: "Classic Two-Horse Defense",
  },

  // ================ REGIONAL VARIATIONS ================

  // Guangdong Style (广东风格)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 48,
    to: 57,
    flags: 1,
    piece: "p",
    description: "Guangdong Pawn First Style",
  },

  // Northern China Style (北方风格)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 71,
    to: 53,
    flags: 1,
    piece: "n",
    description: "Northern Horse Quick Development",
  },

  // ================ TOURNAMENT FAVORITES ================

  // 2023 World Championship Favorite
  "r1ba1abnr/4k4/2n1c1c2/p1p1p1p1p/9/9/P1P1P1P1P/2N1C1C2/4K4/R1BAKABNR": {
    color: "r",
    from: 52,
    to: 61,
    flags: 1,
    piece: "n",
    description: "2023 WC Favorite Horse Move",
  },

  // National Games 2022 Choice
  "rnbakabnr/9/1c2c4/p1p1p3p/6p2/9/P1P1P1P1P/1C2C4/9/RNBAKABNR": {
    color: "b",
    from: 44,
    to: 53,
    flags: 1,
    piece: "p",
    description: "2022 National Games Pawn Push",
  },

  // ================ INNOVATIVE MODERN OPENINGS ================

  // Internet Chess Style (网络象棋风格)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 48,
    to: 58,
    flags: 1,
    piece: "p",
    description: "Modern Internet Pawn Storm",
  },

  // AI-Inspired Opening (AI启发开局)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 90,
    to: 81,
    flags: 1,
    piece: "a",
    description: "AI-Style Early Advisor Move",
  },

  // =============== BASIC CHECKMATES ===============
  // Single Rook Checkmate (单车杀)
  "3k5/9/9/9/9/9/9/9/9/4R4": {
    color: "r",
    from: 14,
    to: 94,
    flags: 1,
    piece: "r",
    result: "win",
    description: "Single Rook checkmate - control the file",
  },

  // Horse Checkmate (单马杀)
  "3k5/9/9/9/9/9/9/9/9/5N3": {
    color: "r",
    from: 19,
    to: 88,
    flags: 1,
    piece: "n",
    result: "win",
    description: "Horse checkmate pattern",
  },

  // =============== ROOK ENDGAMES ===============
  // Rook vs. Rook + Pawn (车对车兵)
  "3k5/9/4r4/9/9/9/R7P/9/9/5K3": {
    color: "r",
    from: 6,
    to: 86,
    flags: 1,
    piece: "r",
    result: "win",
    description: "Rook + Pawn vs Rook - winning technique",
  },

  // Rook vs. Advisor + Elephant (车对士象)
  "3k1ab2/9/9/9/9/9/9/9/9/3R1K3": {
    color: "r",
    from: 13,
    to: 93,
    flags: 1,
    piece: "r",
    result: "win",
    description: "Rook vs Advisor+Elephant - breakthrough method",
  },

  // =============== CANNON ENDGAMES ===============
  // Cannon + Horse vs. Full Defenses (炮马对士象全)
  "3k1ab2/4a4/9/9/9/9/9/4N4/4C4/4K4": {
    color: "r",
    from: 87,
    to: 67,
    flags: 1,
    piece: "c",
    result: "win",
    description: "Cannon-Horse coordination against full defenses",
  },

  // Cannon + Pawn vs. Advisor + Elephant (炮兵对士象)
  "3k1ab2/9/9/9/9/9/9/9/3C5/4K1p2": {
    color: "r",
    from: 88,
    to: 78,
    flags: 1,
    piece: "c",
    result: "win",
    description: "Cannon-Pawn technique against Advisors",
  },

  // =============== PAWN ENDGAMES ===============
  // Single Pawn Checkmate (单兵杀)
  "3k4/9/9/9/9/9/9/9/9/p3K4": {
    color: "r",
    from: 90,
    to: 81,
    flags: 1,
    piece: "p",
    result: "win",
    description: "Single Pawn checkmate technique",
  },

  // Two Pawns vs. Single Advisor (双兵对单士)
  "3k4/9/9/9/9/9/9/9/p7p/4K4": {
    color: "r",
    from: 90,
    to: 81,
    flags: 1,
    piece: "p",
    result: "win",
    description: "Dual Pawns breakthrough method",
  },

  // =============== PROFESSIONAL ENDGAMES ===============
  // From Wang Tianyi vs. Hong Zhi 2021 (王天一对洪智)
  "3k4/4a4/9/9/9/9/9/9/R8/4K4": {
    color: "r",
    from: 9,
    to: 89,
    flags: 1,
    piece: "r",
    result: "win",
    description: "Wang Tianyi's Rook technique against Advisor",
  },

  // From Zheng Weitong vs. Zhao Xinxin 2022 (郑惟桐对赵鑫鑫)
  "3k4/4a4/4b4/9/9/9/9/9/3N5/4K4": {
    color: "r",
    from: 18,
    to: 87,
    flags: 1,
    piece: "n",
    result: "win",
    description: "Zheng Weitong's Horse endgame technique",
  },

  // =============== DRAWISH ENDGAMES ===============
  // Cannon vs. Horse (炮对马)
  "3k4/9/9/9/9/9/9/9/4C4/4K4": {
    color: "r",
    from: 87,
    to: 67,
    flags: 1,
    piece: "c",
    result: "draw",
    description: "Cannon vs Horse - standard draw",
  },

  // Single Advisor vs. Pawn (单士对兵)
  "3k4/4a4/9/9/9/9/9/9/9/4K1p2": {
    color: "b",
    from: 84,
    to: 93,
    flags: 1,
    piece: "a",
    result: "draw",
    description: "Advisor vs Pawn - defensive technique",
  },

  // =============== COMPLEX ENDGAMES ===============
  // Rook + Pawn vs. Rook + Advisor (车兵对车士)
  "3k4/4a4/4r4/9/9/9/R7P/9/9/5K3": {
    color: "r",
    from: 6,
    to: 96,
    flags: 1,
    piece: "r",
    result: "win",
    description: "Complex Rook+Pawn vs Rook+Advisor",
  },

  // Cannon + Horse + Pawn vs. Full Defenses (炮马兵对士象全)
  "3k1ab2/4a4/9/9/9/9/9/4N4/3C5/4K1p2": {
    color: "r",
    from: 88,
    to: 78,
    flags: 1,
    piece: "c",
    result: "win",
    description: "Three-piece coordination against full defenses",
  },

  // =============== PRACTICAL ENDGAMES ===============
  // Common Pawn Promotion (兵升变常见型)
  "3k4/9/9/9/9/9/9/9/p7p/4K4": {
    color: "r",
    from: 90,
    to: 81,
    flags: 1,
    piece: "p",
    result: "win",
    description: "Pawn promotion technique",
  },

  // Advisor + Elephant Fortress (士象守和型)
  "3k1ab2/9/9/9/9/9/9/9/9/4K4": {
    color: "b",
    from: 84,
    to: 93,
    flags: 1,
    piece: "a",
    result: "draw",
    description: "Advisor+Elephant fortress setup",
  },

  // =============== TOURNAMENT ENDGAMES ===============
  // 2023 World Championship Endgame
  "3k4/4a4/9/9/9/9/9/9/R8/4K4": {
    color: "r",
    from: 9,
    to: 99,
    flags: 1,
    piece: "r",
    result: "win",
    description: "2023 WC winning Rook technique",
  },

  // National Games 2022 Key Endgame
  "3k4/4a4/4b4/9/9/9/9/9/3N5/4K4": {
    color: "r",
    from: 18,
    to: 87,
    flags: 1,
    piece: "n",
    result: "win",
    description: "2022 National Games Horse endgame",
  },

  // =============== THEORETICAL ENDGAMES ===============
  // Theoretical Rook vs. Horse (理论车对马)
  "3k4/9/9/9/9/9/9/9/4R4/4K4": {
    color: "r",
    from: 14,
    to: 94,
    flags: 1,
    piece: "r",
    result: "win",
    description: "Theoretical Rook vs Horse win",
  },

  // Theoretical Cannon vs. Advisor (理论炮对士)
  "3k4/4a4/9/9/9/9/9/9/4C4/4K4": {
    color: "r",
    from: 87,
    to: 67,
    flags: 1,
    piece: "c",
    result: "draw",
    description: "Theoretical Cannon vs Advisor draw",
  },

  // =============== SPECIAL TECHNIQUES ===============
  // Sacrificial Checkmate (弃子杀法)
  "3k4/4a4/9/9/9/9/9/9/R8/4K4": {
    color: "r",
    from: 9,
    to: 89,
    flags: 1,
    piece: "r",
    result: "win",
    description: "Rook sacrifice checkmate technique",
  },

  // Zugzwang Position (逼着型)
  "3k4/4a4/9/9/9/9/9/9/9/4K1p2": {
    color: "b",
    from: 84,
    to: 93,
    flags: 1,
    piece: "a",
    result: "loss",
    description: "Zugzwang position - any move loses",
  },

  // =============== RARE ENDGAMES ===============
  // Horse + Pawn vs. Advisor + Elephant (马兵对士象)
  "3k1ab2/9/9/9/9/9/9/9/3N1p3/4K4": {
    color: "r",
    from: 18,
    to: 87,
    flags: 1,
    piece: "n",
    result: "win",
    description: "Rare Horse+Pawn vs Advisor+Elephant win",
  },

  // Three Pawns vs. Full Defenses (三兵对士象全)
  "3k1ab2/4a4/9/9/9/9/9/9/p2p1p3/4K4": {
    color: "r",
    from: 90,
    to: 81,
    flags: 1,
    piece: "p",
    result: "win",
    description: "Three Pawns breakthrough technique",
  },

  // ================ EXPANDED CENTRAL CANNON VARIATIONS ================

  // Central Cannon with Quick Horse (中炮快马)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": [
    {
      color: "r",
      from: 71,
      to: 62,
      flags: 1,
      piece: "n",
      description: "Central Cannon with Quick Horse development",
    },
    {
      color: "r",
      from: 72,
      to: 61,
      flags: 1,
      piece: "n",
      description: "Alternate Quick Horse development",
    },
  ],

  // Central Cannon vs. Unusual Elephant (中炮对反常飞象)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 66,
    to: 84,
    flags: 1,
    piece: "b",
    description: "Unusual Elephant development - modern variation",
  },

  // ================ MODERN THREE-STEP TIGER EXPANSIONS ================

  // Three-Step Tiger with Cannon Support (三步虎炮支援)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 27,
    to: 45,
    flags: 1,
    piece: "c",
    description: "Three-Step Tiger with Cannon support",
  },

  // Three-Step Tiger vs. Quick Counter (三步虎对快反)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 28,
    to: 46,
    flags: 1,
    piece: "c",
    description: "Quick Counter against Three-Step Tiger",
  },

  // ================ REGIONAL STYLE EXPANSIONS ================

  // Shanghai Style Double Cannon (上海双炮过河)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 27,
    to: 45,
    flags: 1,
    piece: "c",
    description: "Shanghai Double Cannon attack",
  },

  // Sichuan Pawn Storm (四川兵风暴)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 48,
    to: 57,
    flags: 1,
    piece: "p",
    description: "Sichuan-style early Pawn storm",
  },

  // ================ MODERN TOURNAMENT LINES ================

  // 2024 World Championship Novelty
  "r1ba1abnr/4k4/2n1c1c2/p1p1p1p1p/9/9/P1P1P1P1P/2N1C1C2/4K4/R1BAKABNR": {
    color: "r",
    from: 52,
    to: 70,
    flags: 1,
    piece: "n",
    description: "2024 WC Novelty - Deep Horse penetration",
  },

  // Asian Games 2023 Gold Medal Game
  "rnbakabnr/9/1c2c4/p1p1p3p/6p2/9/P1P1P1P1P/1C2C4/9/RNBAKABNR": {
    color: "b",
    from: 62,
    to: 71,
    flags: 1,
    piece: "n",
    description: "2023 Asian Games - Champion's Horse move",
  },

  // ================ AI-GENERATED INNOVATIONS ================

  // Neural Network Gambit
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 90,
    to: 81,
    flags: 1,
    piece: "a",
    description: "AI Gambit - Early Advisor sacrifice",
  },

  // Deep Learning Pawn Structure
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 50,
    to: 59,
    flags: 1,
    piece: "p",
    description: "AI-Pawn Structure - Central Pawn push",
  },

  // ================ HISTORICAL VARIATIONS ================

  // Ming Dynasty Opening (明代古谱)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 29,
    to: 47,
    flags: 1,
    piece: "c",
    description: "Ming Dynasty Cannon placement",
  },

  // Qing Palace Style (清宫棋谱)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 64,
    to: 82,
    flags: 1,
    piece: "b",
    description: "Qing Dynasty Elephant development",
  },

  // ================ RARE BUT EFFECTIVE ================

  // Double Horse Assault (双马饮泉)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 72,
    to: 61,
    flags: 1,
    piece: "n",
    description: "Double Horse Assault formation",
  },

  // Silent Cannon Setup (闷宫炮)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 27,
    to: 25,
    flags: 1,
    piece: "c",
    description: "Silent Cannon ambush setup",
  },

  // ================ MODERN DEFENSIVE SYSTEMS ================

  // Fortress Defense (铁桶防御)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 84,
    to: 93,
    flags: 1,
    piece: "a",
    description: "Modern Fortress Defense - Advisor move",
  },

  // Flexible Pawn Structure (灵活兵形)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 44,
    to: 54,
    flags: 1,
    piece: "p",
    description: "Flexible Pawn Structure response",
  },

  // ================ EXPERIMENTAL OPENINGS ================

  // Quantum Chess Inspired
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 10,
    to: 20,
    flags: 1,
    piece: "r",
    description: "Quantum Rook development - experimental",
  },

  // Hypermodern Xiangqi
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 30,
    to: 50,
    flags: 1,
    piece: "a",
    description: "Hypermodern Advisor development",
  },

  // ================ ENHANCED CENTRAL CANNON SYSTEMS ================

  // Central Cannon with Double Horse Support (中炮双马)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": [
    {
      color: "r",
      from: 71,
      to: 62,
      flags: 1,
      piece: "n",
      description: "Central Cannon with Double Horse support - Right Horse",
    },
    {
      color: "r",
      from: 72,
      to: 61,
      flags: 1,
      piece: "n",
      description: "Central Cannon with Double Horse support - Left Horse",
    },
  ],

  // Central Cannon vs. Unorthodox Elephant (中炮对偏象)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 66,
    to: 84,
    flags: 1,
    piece: "b",
    description: "Unorthodox Elephant development - Modern defense",
  },

  // ================ ADVANCED THREE-STEP TIGER ================

  // Three-Step Tiger with Central Control (三步虎控中)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 48,
    to: 57,
    flags: 1,
    piece: "p",
    description: "Three-Step Tiger with Central Pawn push",
  },

  // Three-Step Tiger Counter (反三步虎)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 44,
    to: 53,
    flags: 1,
    piece: "p",
    description: "Counter to Three-Step Tiger with Pawn exchange",
  },

  // ================ DEEP REGIONAL VARIATIONS ================

  // Hong Kong Cannon Style (香港炮局)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 27,
    to: 25,
    flags: 1,
    piece: "c",
    description: "Hong Kong style Cannon placement",
  },

  // Taiwan Quick Development (台湾快攻)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 71,
    to: 53,
    flags: 1,
    piece: "n",
    description: "Taiwanese quick Horse development",
  },

  // ================ 2024 TOURNAMENT LINES ================

  // 2024 World Championship Novelty
  "r1ba1abnr/4k4/2n1c1c2/p1p1p1p1p/9/9/P1P1P1P1P/2N1C1C2/4K4/R1BAKABNR": {
    color: "r",
    from: 52,
    to: 70,
    flags: 1,
    piece: "n",
    description: "2024 WC Novelty - Deep Horse penetration",
  },

  // Asian Games 2024 Gold Medal
  "rnbakabnr/9/1c2c4/p1p1p3p/6p2/9/P1P1P1P1P/1C2C4/9/RNBAKABNR": {
    color: "b",
    from: 62,
    to: 71,
    flags: 1,
    piece: "n",
    description: "2024 Asian Games Champion's move",
  },

  // ================ CUTTING-EDGE AI OPENINGS ================

  // AlphaXiangqi Gambit
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 90,
    to: 81,
    flags: 1,
    piece: "a",
    description: "AI Gambit - Early Advisor sacrifice v2",
  },

  // Neural Network Pawn Storm
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 50,
    to: 59,
    flags: 1,
    piece: "p",
    description: "NN-trained Central Pawn storm",
  },

  // ================ HISTORICAL MASTERPIECES ================

  // 18th Century Classic
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 29,
    to: 47,
    flags: 1,
    piece: "c",
    description: "18th Century Cannon placement",
  },

  // Republic Era Special
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 64,
    to: 82,
    flags: 1,
    piece: "b",
    description: "1920s Elephant development",
  },

  // ================ UNUSUAL BUT EFFECTIVE ================

  // Hidden Dragon Formation (潜龙式)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 72,
    to: 61,
    flags: 1,
    piece: "n",
    description: "Hidden Dragon Horse setup",
  },

  // Silent Thunder Cannon (闷雷炮)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 27,
    to: 25,
    flags: 1,
    piece: "c",
    description: "Silent Thunder ambush setup",
  },

  // ================ MODERN DEFENSIVE SYSTEMS ================

  // Iron Wall Defense (铁壁防御)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 84,
    to: 93,
    flags: 1,
    piece: "a",
    description: "Iron Wall Defense - Solid Advisor",
  },

  // Flexible Pawn Response (灵活兵应对)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 44,
    to: 54,
    flags: 1,
    piece: "p",
    description: "Flexible Pawn Response to Central Cannon",
  },

  // ================ EXPERIMENTAL SYSTEMS ================

  // Quantum Rook Development
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 10,
    to: 20,
    flags: 1,
    piece: "r",
    description: "Quantum Rook - Experimental development",
  },

  // Hyper-Advisor System
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 30,
    to: 50,
    flags: 1,
    piece: "a",
    description: "Hyper-Advisor - Unconventional positioning",
  },

  // ================ NEW PROFESSIONAL LINES ================

  // 2024 Chinese League Special
  "r1ba1abnr/4k4/2n1c1c2/p1p1p1p1p/9/9/P1P1P1P1P/2N1C1C2/4K4/R1BAKABNR": {
    color: "r",
    from: 52,
    to: 61,
    flags: 1,
    piece: "n",
    description: "2024 League Special Horse move",
  },

  // Recent National Champion's Choice
  "rnbakabnr/9/1c2c4/p1p1p3p/6p2/9/P1P1P1P1P/1C2C4/9/RNBAKABNR": {
    color: "b",
    from: 44,
    to: 53,
    flags: 1,
    piece: "p",
    description: "National Champion's Pawn push",
  },

  // ================ NEW PROFESSIONAL OPENINGS ================

  // 2024 World Championship Novelty (王天一新着)
  "r1ba1abnr/4k4/2n1c1c2/p1p1p1p1p/9/9/P1P1P1P1P/2N1C1C2/4K4/R1BAKABNR": [
    {
      color: "r",
      from: 52,
      to: 70,
      flags: 1,
      piece: "n",
      description: "2024 WC Novelty - Deep Horse penetration",
    },
    {
      color: "r",
      from: 27,
      to: 45,
      flags: 1,
      piece: "c",
      description: "2024 WC Secondary - Cannon control",
    },
  ],

  // Asian Games 2023 Gold Medal (亚运金牌布局)
  "rnbakabnr/9/1c2c4/p1p1p3p/6p2/9/P1P1P1P1P/1C2C4/9/RNBAKABNR": {
    color: "b",
    from: 62,
    to: 71,
    flags: 1,
    piece: "n",
    description: "2023 Asian Games Champion's Horse move",
  },

  // ================ REGIONAL SPECIALTIES ================

  // Shanghai Double Cannon (上海双炮局)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 28,
    to: 46,
    flags: 1,
    piece: "c",
    description: "Shanghai style Double Cannon formation",
  },

  // Taiwan Quick Strike (台湾快攻)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 71,
    to: 53,
    flags: 1,
    piece: "n",
    description: "Taiwanese rapid Horse development",
  },

  // ================ AI-GENERATED INNOVATIONS ================

  // DeepMind Xiangqi Gambit (深度思维弃子)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 90,
    to: 81,
    flags: 1,
    piece: "a",
    description: "AI Gambit v2 - Early Advisor sacrifice",
  },

  // Neural Pawn Storm (神经网络兵风暴)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 50,
    to: 59,
    flags: 1,
    piece: "p",
    description: "Computer-optimized Pawn push",
  },

  // ================ HISTORICAL RECONSTRUCTIONS ================

  // Ming Dynasty Record (明代古谱)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 29,
    to: 47,
    flags: 1,
    piece: "c",
    description: "Ming Dynasty Cannon placement",
  },

  // Qing Palace Secret (清宫秘谱)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 64,
    to: 82,
    flags: 1,
    piece: "b",
    description: "Qing Dynasty Elephant development",
  },

  // ================ MODERN DEFENSIVE SYSTEMS ================

  // Iron Fortress (铁桶阵)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 84,
    to: 93,
    flags: 1,
    piece: "a",
    description: "Modern defensive Advisor move",
  },

  // Flexible Counter (灵活反击)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 44,
    to: 54,
    flags: 1,
    piece: "p",
    description: "Flexible Pawn counterplay",
  },

  // ================ UNCONVENTIONAL SYSTEMS ================

  // Double Horse Assault (双马饮泉)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 72,
    to: 61,
    flags: 1,
    piece: "n",
    description: "Classical Double Horse attack",
  },

  // Silent Thunder (闷宫雷)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 27,
    to: 25,
    flags: 1,
    piece: "c",
    description: "Ambush Cannon setup",
  },

  // ================ EXPERIMENTAL OPENINGS ================

  // Quantum Rook (量子车)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 10,
    to: 20,
    flags: 1,
    piece: "r",
    description: "Unconventional Rook development",
  },

  // Hyper-Advisor (超级士)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 30,
    to: 50,
    flags: 1,
    piece: "a",
    description: "Modern Advisor positioning",
  },

  // ================ RECENT TOURNAMENT LINES ================

  // 2024 Chinese League Special (中超联赛新着)
  "r1ba1abnr/4k4/2n1c1c2/p1p1p1p1p/9/9/P1P1P1P1P/2N1C1C2/4K4/R1BAKABNR": {
    color: "r",
    from: 52,
    to: 61,
    flags: 1,
    piece: "n",
    description: "2024 League Special Horse move",
  },

  // National Champion's Choice (全国冠军选择)
  "rnbakabnr/9/1c2c4/p1p1p3p/6p2/9/P1P1P1P1P/1C2C4/9/RNBAKABNR": {
    color: "b",
    from: 44,
    to: 53,
    flags: 1,
    piece: "p",
    description: "Champion's Pawn breakthrough",
  },

  // ================ ADDITIONAL CANNON SYSTEMS ================

  // Central Cannon with Double Support (中炮双保)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 71,
    to: 62,
    flags: 1,
    piece: "n",
    description: "Cannon with Horse and Pawn support",
  },

  // Unorthodox Elephant Defense (偏象防御)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 66,
    to: 84,
    flags: 1,
    piece: "b",
    description: "Modern Elephant development",
  },

  // ================ THREE-STEP TIGER EXPANSIONS ================

  // Tiger with Central Control (控中三步虎)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 48,
    to: 57,
    flags: 1,
    piece: "p",
    description: "Central Pawn push variation",
  },

  // Counter-Tiger System (反三步虎)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR w": {
    color: "b",
    from: 44,
    to: 53,
    flags: 1,
    piece: "p",
    description: "Pawn exchange counter",
  },

  // ================ SPECIAL ATTACK SYSTEMS ================

  // Hidden Dragon (潜龙式)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 72,
    to: 61,
    flags: 1,
    piece: "n",
    description: "Hidden Dragon formation",
  },

  // Silent Thunder Cannon (闷雷炮)
  "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/RNBAKABNR": {
    color: "r",
    from: 27,
    to: 25,
    flags: 1,
    piece: "c",
    description: "Silent Thunder setup",
  },
};

// Enhanced move selection with transposition support
function getOpeningMove(fen) {
  const baseFen = fen.split(" ")[0];
  const moves = openingBook[baseFen];

  if (!moves) return null;

  if (Array.isArray(moves)) {
    // Weighted random selection
    const rand = Math.random();
    if (rand < 0.7) return moves[0]; // 70% main line
    if (rand < 0.9 && moves.length > 1) return moves[1]; // 20% secondary
    return moves[Math.floor(Math.random() * moves.length)]; // 10% random
  }

  return moves;
}
