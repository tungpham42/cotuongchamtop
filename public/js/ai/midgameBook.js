const midgameBook = {
  // ================ CANNON POSITIONAL PATTERNS ================
  // Central Cannon with Linked Horses (中炮连环马)
  "r1bakab1r/9/2n1c1cn1/p1p1p1p1p/9/6P2/P1P1P3P/2N1C1CN1/9/R1BAKAB1R": {
    color: "r",
    moves: [
      {
        from: 45,
        to: 63,
        flags: 1,
        piece: "c",
        description: "Cannon pins horse to prepare attack",
      },
      {
        from: 52,
        to: 63,
        flags: 1,
        piece: "n",
        description: "Horse sacrifice to open file",
      },
    ],
    plan: "Control center and prepare pawn push",
  },

  // ================ HORSE OUTPOST STRATEGIES ================
  // Strong Horse Outpost (马位优势)
  "r1ba1abnr/4k4/2n1c1c2/p1p1p1p1p/9/9/P1P1P1P1P/2N1C1CN1/4K4/R3A1B1R": {
    color: "r",
    moves: [
      {
        from: 52,
        to: 63,
        flags: 1,
        piece: "n",
        description: "Establish outpost horse",
      },
      {
        from: 71,
        to: 62,
        flags: 1,
        piece: "n",
        description: "Support horse with second knight",
      },
    ],
    plan: "Dominate with horse outpost and coordinate pieces",
  },

  // ================ PAWN STRUCTURE PLANS ================
  // Connected Passed Pawns (连通通路兵)
  "rnbakab1r/9/1c2c1n2/p1p1p1p1p/9/3P5/P1P1P1P1P/1C2C1N2/9/RNBAKAB1R": {
    color: "r",
    moves: [
      {
        from: 48,
        to: 57,
        flags: 1,
        piece: "p",
        description: "Push central pawn",
      },
      {
        from: 50,
        to: 59,
        flags: 1,
        piece: "p",
        description: "Create connected passed pawns",
      },
    ],
    plan: "Advance pawns in tandem to create weaknesses",
  },

  // ================ PROFESSIONAL MIDGAME POSITIONS ================
  // Wang Tianyi's Typical Midgame (王天一中局典型)
  "r1ba1abnr/4k4/2n1c1c2/p1p3p1p/4p4/6P2/P1P1P3P/2N1C1CN1/4K4/R1BA1AB1R": {
    color: "r",
    moves: [
      {
        from: 45,
        to: 63,
        flags: 1,
        piece: "c",
        description: "Cannon repositioning for attack",
      },
      {
        from: 52,
        to: 43,
        flags: 1,
        piece: "n",
        description: "Horse joins the attack",
      },
    ],
    plan: "Coordinate pieces for kingside attack",
  },

  // ================ ATTACKING PATTERNS ================
  // Double Cannon Battery (双炮重炮)
  "r1bakab1r/9/2n1c1cn1/p1p1p1p1p/9/9/P1P1P1P1P/1CN1C1N2/4K4/R3A1B1R": {
    color: "r",
    moves: [
      {
        from: 27,
        to: 45,
        flags: 1,
        piece: "c",
        description: "Set up double cannon battery",
      },
      {
        from: 45,
        to: 63,
        flags: 1,
        piece: "c",
        description: "Prepare checkmating net",
      },
    ],
    plan: "Create unstoppable double cannon threat",
  },

  // ================ DEFENSIVE STRUCTURES ================
  // Iron Defense (铁门栓防御)
  "rnbakab1r/9/1c2c1n2/p1p1p1p1p/9/9/P1P1P1P1P/1C2C1N2/4K4/RNBA1AB1R": {
    color: "b",
    moves: [
      {
        from: 84,
        to: 93,
        flags: 1,
        piece: "a",
        description: "Solidify the defense",
      },
      {
        from: 64,
        to: 82,
        flags: 1,
        piece: "b",
        description: "Elephant blocks potential attacks",
      },
    ],
    plan: "Maintain solid structure and wait for mistakes",
  },

  // ================ TRANSITIONAL POSITIONS ================
  // From Opening to Midgame (开局转中局)
  "r1ba1abnr/4k4/2n1c1c2/p1p1p1p1p/9/9/P1P1P1P1P/2N1C1CN1/4K4/R1BA1AB1R": {
    color: "r",
    moves: [
      {
        from: 48,
        to: 57,
        flags: 1,
        piece: "p",
        description: "Begin central pawn push",
      },
      {
        from: 52,
        to: 63,
        flags: 1,
        piece: "n",
        description: "Develop horse for attack",
      },
    ],
    plan: "Transition from development to concrete plans",
  },

  // ================ PIECE ACTIVATION PATTERNS ================
  // Rook Lift Technique (车抬升技术)
  "r1bakab1r/9/1cn1c1n2/p1p1p1p1p/9/9/P1P1P1P1P/1CN1C1N2/4K4/R3A1B1R": {
    color: "r",
    moves: [
      {
        from: 10,
        to: 30,
        flags: 1,
        piece: "r",
        description: "Rook lift to central files",
      },
      {
        from: 9,
        to: 29,
        flags: 1,
        piece: "r",
        description: "Double rook battery",
      },
    ],
    plan: "Activate rooks for maximum influence",
  },

  // ================ MODERN COMPUTER-ANALYZED POSITIONS ================
  // AI-Recommended Plan (AI推荐计划)
  "r1ba1abnr/4k4/2n1c1c2/p1p3p1p/4p4/6P2/P1P1P3P/2N1C1CN1/4K4/R1BA1AB1R": {
    color: "r",
    moves: [
      {
        from: 45,
        to: 63,
        flags: 1,
        piece: "c",
        description: "Computer-preferred cannon move",
      },
      {
        from: 52,
        to: 43,
        flags: 1,
        piece: "n",
        description: "Horse maneuver found by AI",
      },
    ],
    plan: "Apply pressure on both wings simultaneously",
  },
};

// Enhanced move selection with plan consideration
function getMidgameMove(fen) {
  const position = midgameBook[fen];
  if (!position) return null;

  // Select move based on position type and plan
  if (position.moves.length > 1) {
    // Prefer moves that match the described plan
    const planMoves = position.moves.filter((move) =>
      move.description.includes(position.plan.split(" ")[0])
    );
    if (planMoves.length > 0) {
      return planMoves[Math.floor(Math.random() * planMoves.length)];
    }
    return position.moves[Math.floor(Math.random() * position.moves.length)];
  }

  return position.moves[0];
}
