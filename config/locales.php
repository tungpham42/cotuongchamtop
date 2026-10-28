<?php

return [
    'default' => 'vi',

    'supported' => ['vi', 'en', 'ja', 'ko', 'zh', 'de', 'fr', 'es', 'ru'],

    'labels' => [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
        'ja' => '日本語',
        'ko' => '한국어',
        'zh' => '中文',
        'de' => 'Deutsch',
        'fr' => 'Français',
        'es' => 'Español',
        'ru' => 'Русский',
    ],

    'flags' => [
        'vi' => 'vn',
        'en' => 'us',
        'ja' => 'jp',
        'ko' => 'kr',
        'zh' => 'cn',
        'de' => 'de',
        'fr' => 'fr',
        'es' => 'es',
        'ru' => 'ru',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy SEO paths
    |--------------------------------------------------------------------------
    |
    | These paths preserve the currently indexed public URLs. Refactors should
    | route these paths to shared controllers/views instead of changing them.
    |
    */
    'paths' => [
        // App/Home
        'home' => ['vi' => '/', 'en' => 'en', 'ja' => 'ja', 'ko' => 'ko', 'zh' => 'zh', 'de' => 'de', 'fr' => 'fr', 'es' => 'es', 'ru' => 'ru'],

        // Rooms / Lobby
        'room.list'   => ['vi' => 'sanh-cho', 'en' => 'lobby', 'ja' => 'heya-ichiran', 'ko' => 'bang-moglog', 'zh' => 'fangjianliebiao', 'de' => 'raumliste', 'fr' => 'liste-des-salons', 'es' => 'lista-de-salas', 'ru' => 'spisok-komnat'],
        'room.host'   => ['vi' => 'phong/{code}', 'en' => 'room/{code}', 'ja' => 'rumu/{code}', 'ko' => 'bang/{code}', 'zh' => 'fangjian/{code}', 'de' => 'raum/{code}', 'fr' => 'salon/{code}', 'es' => 'sala/{code}', 'ru' => 'komnata/{code}'],
        'room.guest'  => ['vi' => 'phong/{code}/khach', 'en' => 'room/{code}/guest', 'ja' => 'rumu/{code}/geesuto', 'ko' => 'bang/{code}/bangmun', 'zh' => 'fangjian/{code}/zhuke', 'de' => 'raum/{code}/gast', 'fr' => 'salon/{code}/invite', 'es' => 'sala/{code}/invitado', 'ru' => 'komnata/{code}/gost'],
        'room.random' => ['vi' => 'phong/{code}/ngau-nhien', 'en' => 'room/{code}/random', 'ja' => 'rumu/{code}/randamu', 'ko' => 'bang/{code}/mujag-wiui', 'zh' => 'fangjian/{code}/suijide', 'de' => 'raum/{code}/zufall', 'fr' => 'salon/{code}/aleatoire', 'es' => 'sala/{code}/aleatorio', 'ru' => 'komnata/{code}/sluchaynyy'],
        'room.watch'  => ['vi' => 'phong/{code}/theo-doi', 'en' => 'room/{code}/watch', 'ja' => 'rumu/{code}/miru', 'ko' => 'bang/{code}/boda', 'zh' => 'fangjian/{code}/kan', 'de' => 'raum/{code}/zuschauen', 'fr' => 'salon/{code}/observer', 'es' => 'sala/{code}/observar', 'ru' => 'komnata/{code}/nablyudat'],
        'room.red'    => ['vi' => 'phong/{code}/do', 'en' => 'room/{code}/red', 'ja' => 'rumu/{code}/aka', 'ko' => 'bang/{code}/ppalgan', 'zh' => 'fangjian/{code}/hongse', 'de' => 'raum/{code}/rot', 'fr' => 'salon/{code}/rouge', 'es' => 'sala/{code}/rojo', 'ru' => 'komnata/{code}/krasnye'],
        'room.black'  => ['vi' => 'phong/{code}/den', 'en' => 'room/{code}/black', 'ja' => 'rumu/{code}/kuro', 'ko' => 'bang/{code}/geom-eunsaeg', 'zh' => 'fangjian/{code}/heise', 'de' => 'raum/{code}/schwarz', 'fr' => 'salon/{code}/noir', 'es' => 'sala/{code}/negro', 'ru' => 'komnata/{code}/chernye'],

        // Static Pages
        'terms'   => ['vi' => 'dieu-khoan', 'en' => 'terms-and-conditions', 'ja' => 'riyo-kiyaku', 'ko' => 'iyong-yaggwan', 'zh' => 'tiaokuan-he-tiaojian', 'de' => 'nutzungsbedingungen', 'fr' => 'conditions-generales', 'es' => 'terminos-y-condiciones', 'ru' => 'usloviya-ispolzovaniya'],
        'privacy' => ['vi' => 'chinh-sach-bao-mat', 'en' => 'privacy-policy', 'ja' => 'puraibashi-porishi', 'ko' => 'gaeinjeongbo-cheolibangchim', 'zh' => 'yinsi-zhengce', 'de' => 'datenschutz', 'fr' => 'politique-de-confidentialite', 'es' => 'politica-de-privacidad', 'ru' => 'politika-konfidentsialnosti'],
        'about'   => ['vi' => 'gioi-thieu', 'en' => 'about-us', 'ja' => 'yaku', 'ko' => 'urie-daehae', 'zh' => 'guanyuwomens', 'de' => 'ueber-uns', 'fr' => 'a-propos', 'es' => 'sobre-nosotros', 'ru' => 'o-nas'],
        'contact' => ['vi' => 'lien-he', 'en' => 'contact-us', 'ja' => 'kontakuto', 'ko' => 'mun-uihagi', 'zh' => 'lianxiwomen', 'de' => 'kontakt', 'fr' => 'contact', 'es' => 'contacto', 'ru' => 'kontakty'],

        // Gameplay Modes
        'human.play'   => ['vi' => 'choi-mot-minh', 'en' => 'play-alone', 'ja' => 'ichi-nin-de-asobu', 'ko' => 'honja-nolda', 'zh' => 'duchu', 'de' => 'allein-spielen', 'fr' => 'jouer-seul', 'es' => 'jugar-solo', 'ru' => 'igrat-odnomu'],
        'puzzle.setup' => ['vi' => 'co-the', 'en' => 'puzzle', 'ja' => 'pazuru', 'ko' => 'peojeul', 'zh' => 'mi', 'de' => 'puzzle', 'fr' => 'probleme', 'es' => 'problema', 'ru' => 'zadacha'],
        'puzzle.board' => ['vi' => 'co-the/{board}', 'en' => 'puzzle/{board}', 'ja' => 'pazuru/{board}', 'ko' => 'peojeul/{board}', 'zh' => 'mi/{board}', 'de' => 'puzzle/{board}', 'fr' => 'probleme/{board}', 'es' => 'problema/{board}', 'ru' => 'zadacha/{board}'],

        // AI / Bots
        'board.self'         => ['vi' => 'ban-co/{fen}', 'en' => 'board/{fen}', 'ja' => 'bodo/{fen}', 'ko' => 'bodeu/{fen}', 'zh' => 'ban/{fen}', 'de' => 'brett/{fen}', 'fr' => 'plateau/{fen}', 'es' => 'tablero/{fen}', 'ru' => 'doska/{fen}'],
        'board.ai.easiest'   => ['vi' => 'ban-co-de-nhat/{fen}', 'en' => 'easiest-board/{fen}', 'ja' => 'mottomo-kantanna-bodo/{fen}', 'ko' => 'gajang-swiun-bodeu/{fen}', 'zh' => 'zuijiandandeban/{fen}', 'de' => 'einfachstes-brett/{fen}', 'fr' => 'plateau-tres-facile/{fen}', 'es' => 'tablero-facilisimo/{fen}', 'ru' => 'samaya-legkaya-doska/{fen}'],
        'board.ai.newbie'    => ['vi' => 'ban-co-moi-choi/{fen}', 'en' => 'newbie-board/{fen}', 'ja' => 'shoshinsha-bodo/{fen}', 'ko' => 'nyubi-bodeu/{fen}', 'zh' => 'xinshouban/{fen}', 'de' => 'anfaenger-brett/{fen}', 'fr' => 'plateau-debutant/{fen}', 'es' => 'tablero-principiante/{fen}', 'ru' => 'doska-novichka/{fen}'],
        'board.ai.easy'      => ['vi' => 'ban-co-de/{fen}', 'en' => 'easy-board/{fen}', 'ja' => 'kantan-bodo/{fen}', 'ko' => 'iji-bodeu/{fen}', 'zh' => 'jianyiban/{fen}', 'de' => 'leichtes-brett/{fen}', 'fr' => 'plateau-facile/{fen}', 'es' => 'tablero-facil/{fen}', 'ru' => 'legkaya-doska/{fen}'],
        'board.ai.normal'    => ['vi' => 'ban-co-binh-thuong/{fen}', 'en' => 'normal-board/{fen}', 'ja' => 'tsujo-bodo/{fen}', 'ko' => 'nomol-bodeu/{fen}', 'zh' => 'putongban/{fen}', 'de' => 'normales-brett/{fen}', 'fr' => 'plateau-normal/{fen}', 'es' => 'tablero-normal/{fen}', 'ru' => 'normalnaya-doska/{fen}'],
        'board.ai.hard'      => ['vi' => 'ban-co-kho/{fen}', 'en' => 'hard-board/{fen}', 'ja' => 'hado-bodo/{fen}', 'ko' => 'hadeu-bodeu/{fen}', 'zh' => 'yingban/{fen}', 'de' => 'schweres-brett/{fen}', 'fr' => 'plateau-difficile/{fen}', 'es' => 'tablero-dificil/{fen}', 'ru' => 'slozhnaya-doska/{fen}'],
        'board.ai.hardest'   => ['vi' => 'ban-co-kho-nhat/{fen}', 'en' => 'hardest-board/{fen}', 'ja' => 'mottomo-muzukashi-bodo/{fen}', 'ko' => 'gajang-dandanhan-bodeu/{fen}', 'zh' => 'zuiyingban/{fen}', 'de' => 'schwerstes-brett/{fen}', 'fr' => 'plateau-tres-difficile/{fen}', 'es' => 'tablero-dificilisimo/{fen}', 'ru' => 'samaya-slozhnaya-doska/{fen}'],
        'puzzle.ai.solve'    => ['vi' => 'giai-co-the/{fen}', 'en' => 'solve-puzzle/{fen}', 'ja' => 'pazuru-o-toku/{fen}', 'ko' => 'pojeureul-pulda/{fen}', 'zh' => 'jiejuenanti/{fen}', 'de' => 'puzzle-loesen/{fen}', 'fr' => 'resoudre-probleme/{fen}', 'es' => 'resolver-problema/{fen}', 'ru' => 'reshit-zadachu/{fen}'],
        'puzzle.rating'      => ['vi' => 'the-co/{slug}', 'en' => 'puzzle-record/{slug}', 'ja' => 'pazuru-kiroku/{slug}', 'ko' => 'peojeul-girog/{slug}', 'zh' => 'mi-jilu/{slug}', 'de' => 'puzzle-eintrag/{slug}', 'fr' => 'fiche-probleme/{slug}', 'es' => 'registro-problema/{slug}', 'ru' => 'zapis-zadachi/{slug}'],

        // AI Home / Levels
        'ai.home'    => ['vi' => '/', 'en' => 'en', 'ja' => 'ja', 'ko' => 'ko', 'zh' => 'zh', 'de' => 'de', 'fr' => 'fr', 'es' => 'es', 'ru' => 'ru'],
        'ai.easiest' => ['vi' => 'de-nhat', 'en' => 'easiest', 'ja' => 'mottomo-kantan', 'ko' => 'gajang-swiun', 'zh' => 'zuijiandan', 'de' => 'einfachste', 'fr' => 'tres-facile', 'es' => 'facilisimo', 'ru' => 'samyy-legkiy'],
        'ai.newbie'  => ['vi' => 'moi-choi', 'en' => 'newbie', 'ja' => 'shoshinsha', 'ko' => 'nyubi', 'zh' => 'xinshou', 'de' => 'anfaenger', 'fr' => 'debutant', 'es' => 'principiante', 'ru' => 'novichok'],
        'ai.easy'    => ['vi' => 'de-dang', 'en' => 'easy', 'ja' => 'kantan', 'ko' => 'iji', 'zh' => 'jianyiban', 'de' => 'leicht', 'fr' => 'facile', 'es' => 'facil', 'ru' => 'legkiy'],
        'ai.normal'  => ['vi' => 'binh-thuong', 'en' => 'normal', 'ja' => 'tsujo', 'ko' => 'nomol', 'zh' => 'putong', 'de' => 'normalerweise', 'fr' => 'normalement', 'es' => 'normalmente', 'ru' => 'normalnyy'],
        'ai.hard'    => ['vi' => 'kho', 'en' => 'hard', 'ja' => 'hado', 'ko' => 'hadeu', 'zh' => 'ying', 'de' => 'schwer', 'fr' => 'difficile', 'es' => 'dificil', 'ru' => 'slozhnyy'],
        'ai.hardest' => ['vi' => 'kho-nhat', 'en' => 'hardest', 'ja' => 'mottomo-muzukashi', 'ko' => 'gajang-dandanhan', 'zh' => 'zuiying', 'de' => 'schwerste', 'fr' => 'tres-difficile', 'es' => 'dificilisimo', 'ru' => 'samyy-slozhnyy'],
        'ai.master'  => ['vi' => 'kien-tuong', 'en' => 'grandmaster', 'ja' => 'masuta', 'ko' => 'maseuteo', 'zh' => 'dashi', 'de' => 'meister', 'fr' => 'maitre', 'es' => 'maestro', 'ru' => 'grossmeyster'],

        // Application (Dashboard, Profile, Settings)
        'app.dashboard' => ['vi' => 'thi-dau', 'en' => 'compete', 'ja' => 'kyogi', 'ko' => 'gyeong-gi', 'zh' => 'jingzheng', 'de' => 'wettkampf', 'fr' => 'competition', 'es' => 'competir', 'ru' => 'sorevnovanie'],
        'app.history'   => ['vi' => 'lich-su', 'en' => 'history', 'ja' => 'rekishi', 'ko' => 'yeogsa', 'zh' => 'lishi', 'de' => 'verlauf', 'fr' => 'historique', 'es' => 'historial', 'ru' => 'istoriya'],
        'app.ranking'   => ['vi' => 'bang-xep-hang', 'en' => 'ranking', 'ja' => 'rankingu', 'ko' => 'sun-wi', 'zh' => 'paiming', 'de' => 'rangliste', 'fr' => 'classement', 'es' => 'clasificacion', 'ru' => 'reyting'],
        'app.password'  => ['vi' => 'doi-mat-khau', 'en' => 'change-password', 'ja' => 'pasuwado-henko', 'ko' => 'bimilbeonho-byeongyeong', 'zh' => 'genggaimima', 'de' => 'passwort-aendern', 'fr' => 'changer-mot-de-passe', 'es' => 'cambiar-contrasena', 'ru' => 'smenit-parol'],
        'app.name'      => ['vi' => 'doi-ten', 'en' => 'change-name', 'ja' => 'namae-henko', 'ko' => 'ileum-byeongyeong', 'zh' => 'genggaimingcheng', 'de' => 'name-aendern', 'fr' => 'changer-nom', 'es' => 'cambiar-nombre', 'ru' => 'smenit-imya'],
        'app.ui'        => ['vi' => 'doi-giao-dien', 'en' => 'change-ui', 'ja' => 'ui-henko', 'ko' => 'ui-byeongyeong', 'zh' => 'genggai-ui', 'de' => 'oberflaeche-aendern', 'fr' => 'changer-interface', 'es' => 'cambiar-interfaz', 'ru' => 'smenit-oformlenie'],
        'app.profile'   => ['vi' => 'ho-so-cua-toi', 'en' => 'my-profile', 'ja' => 'watashi-no-purofiru', 'ko' => 'nae-peulopil', 'zh' => 'wodeziliao', 'de' => 'mein-profil', 'fr' => 'mon-profil', 'es' => 'mi-perfil', 'ru' => 'moy-profil'],
        'app.player'    => ['vi' => 'ky-thu/{id}', 'en' => 'player/{id}', 'ja' => 'pureya/{id}', 'ko' => 'peulleieo/{id}', 'zh' => 'wanjia/{id}', 'de' => 'spieler/{id}', 'fr' => 'joueur/{id}', 'es' => 'jugador/{id}', 'ru' => 'igrok/{id}'],

        // Auth
        'login'            => ['vi' => 'dang-nhap', 'en' => 'login', 'ja' => 'roguin', 'ko' => 'log-in', 'zh' => 'denglu', 'de' => 'anmelden', 'fr' => 'connexion', 'es' => 'iniciar-sesion', 'ru' => 'vkhod'],
        'register'         => ['vi' => 'dang-ky', 'en' => 'register', 'ja' => 'toroku', 'ko' => 'deunglog', 'zh' => 'zhuce', 'de' => 'registrieren', 'fr' => 'inscription', 'es' => 'registrarse', 'ru' => 'registratsiya'],
        'password.request' => ['vi' => 'quen-mat-khau', 'en' => 'forgot-password', 'ja' => 'pasuwado-wasureta', 'ko' => 'bimilbeonho-ij-eobeolim', 'zh' => 'wangjimima', 'de' => 'passwort-vergessen', 'fr' => 'mot-de-passe-oublie', 'es' => 'olvide-contrasena', 'ru' => 'zabyl-parol'],
        'password.create'  => ['vi' => 'tao-mat-khau', 'en' => 'create-password', 'ja' => 'pasuwado-sakusei', 'ko' => 'bimilbeonho-mandeulgi', 'zh' => 'chuangjianmima', 'de' => 'passwort-erstellen', 'fr' => 'creer-mot-de-passe', 'es' => 'crear-contrasena', 'ru' => 'sozdat-parol'],
        'password.reset'   => ['vi' => 'dat-lai-mat-khau/{token}', 'en' => 'reset-password/{token}', 'ja' => 'pasuwado-risetto/{token}', 'ko' => 'bimilbeonho-jaeseoljeong/{token}', 'zh' => 'chongzhimima/{token}', 'de' => 'passwort-zuruecksetzen/{token}', 'fr' => 'reinitialiser-mot-de-passe/{token}', 'es' => 'restablecer-contrasena/{token}', 'ru' => 'sbros-parolya/{token}'],

        // Auth Actions (Add these to your existing configuration)
        'logout'           => ['vi' => 'dang-xuat', 'en' => 'logout', 'ja' => 'roguauto', 'ko' => 'log-a-us', 'zh' => 'dengchu', 'de' => 'abmelden', 'fr' => 'deconnexion', 'es' => 'cerrar-sesion', 'ru' => 'vykhod'],
        'password.email'   => ['vi' => 'gui-duong-dan-tao-mat-khau', 'en' => 'send-reset-link', 'ja' => 'risetto-rinku-soshin', 'ko' => 'jaeseoljeong-lingkeu-boda', 'zh' => 'fasong-chongzhi-lianjie', 'de' => 'zuruecksetzen-link-senden', 'fr' => 'envoyer-lien-de-reinitialisation', 'es' => 'enviar-enlace-de-restablecimiento', 'ru' => 'otpravit-ssylku-dlya-sbrosa'],
        'password.update'  => ['vi' => 'quen-mat-khau', 'en' => 'forgot-password-update', 'ja' => 'pasuwado-koshin', 'ko' => 'bimilbeonho-eobdeiteu', 'zh' => 'wangjimima-gengxin', 'de' => 'passwort-vergessen-aktualisieren', 'fr' => 'mot-de-passe-oublie-mise-a-jour', 'es' => 'olvide-contrasena-actualizar', 'ru' => 'zabyl-parol-obnovit'],

        // Lists/Misc
        'puzzle.list' => ['vi' => 'tat-ca-the-co', 'en' => 'all-puzzles', 'ja' => 'subete-no-pazuru', 'ko' => 'modeun-peojeul', 'zh' => 'suoyou-mi', 'de' => 'alle-puzzles', 'fr' => 'tous-les-problemes', 'es' => 'todos-los-problemas', 'ru' => 'vse-zadachi'],
        'user.list'   => ['vi' => 'thanh-vien', 'en' => 'members', 'ja' => 'pureya', 'ko' => 'peulleieo', 'zh' => 'wanjia', 'de' => 'mitglieder', 'fr' => 'membres', 'es' => 'miembros', 'ru' => 'uchastniki'],
        'search'      => ['vi' => 'tim-kiem', 'en' => 'search', 'ja' => 'kensaku', 'ko' => 'geomsaeg', 'zh' => 'sousuo', 'de' => 'suche', 'fr' => 'recherche', 'es' => 'buscar', 'ru' => 'poisk'],

        // Tournaments
        'tournaments.index'    => ['vi' => 'giai-dau', 'en' => 'tournaments', 'ja' => 'tonamento', 'ko' => 'toneomeonteu', 'zh' => 'jinbiaosai', 'de' => 'turniere', 'fr' => 'tournois', 'es' => 'torneos', 'ru' => 'turniry'],
        'tournaments.show'     => ['vi' => 'giai-dau/{slug}', 'en' => 'tournament/{slug}', 'ja' => 'tonamento/{slug}', 'ko' => 'toneomeonteu/{slug}', 'zh' => 'jinbiaosai/{slug}', 'de' => 'turnier/{slug}', 'fr' => 'tournoi/{slug}', 'es' => 'torneo/{slug}', 'ru' => 'turnir/{slug}'],
        'tournaments.join'     => ['vi' => 'giai-dau/{slug}/tham-gia', 'en' => 'tournaments/{slug}/join', 'ja' => 'tonamento/{slug}/sanka', 'ko' => 'toneomeonteu/{slug}/chamga', 'zh' => 'jinbiaosai/{slug}/jiaru', 'de' => 'turniere/{slug}/beitreten', 'fr' => 'tournois/{slug}/rejoindre', 'es' => 'torneos/{slug}/unirse', 'ru' => 'turniry/{slug}/prisoedinitsya'],
        'tournaments.generate' => ['vi' => 'giai-dau/{slug}/tao-bang', 'en' => 'tournaments/{slug}/generate-bracket', 'ja' => 'tonamento/{slug}/buraketto-sakusei', 'ko' => 'toneomeonteu/{slug}/daejinpyo-saengseong', 'zh' => 'jinbiaosai/{slug}/shengcheng-duizhentu', 'de' => 'turniere/{slug}/turnierbaum-erstellen', 'fr' => 'tournois/{slug}/generer-tableau', 'es' => 'torneos/{slug}/generar-cuadro', 'ru' => 'turniry/{slug}/sozdat-setku'],
        'tournaments.create'   => ['vi' => 'admin/giai-dau/tao-moi', 'en' => 'admin/tournaments/create', 'ja' => 'admin/tonamento/sakusei', 'ko' => 'admin/toneomeonteu/mandeulgi', 'zh' => 'admin/jinbiaosai/chuangjian', 'de' => 'admin/turniere/erstellen', 'fr' => 'admin/tournois/creer', 'es' => 'admin/torneos/crear', 'ru' => 'admin/turniry/sozdat'],
        'tournaments.store'    => ['vi' => 'admin/giai-dau', 'en' => 'admin/tournaments', 'ja' => 'admin/tonamento', 'ko' => 'admin/toneomeonteu', 'zh' => 'admin/jinbiaosai', 'de' => 'admin/turniere', 'fr' => 'admin/tournois', 'es' => 'admin/torneos', 'ru' => 'admin/turniry'],
        'tournaments.edit'     => ['vi' => 'admin/giai-dau/{slug}/sua', 'en' => 'admin/tournaments/{slug}/edit', 'ja' => 'admin/tonamento/{slug}/henshuu', 'ko' => 'admin/toneomeonteu/{slug}/pyeongseong', 'zh' => 'admin/jinbiaosai/{slug}/bianji', 'de' => 'admin/turniere/{slug}/bearbeiten', 'fr' => 'admin/tournois/{slug}/modifier', 'es' => 'admin/torneos/{slug}/editar', 'ru' => 'admin/turniry/{slug}/izmenit'],
        'tournaments.update'   => ['vi' => 'admin/giai-dau/{slug}', 'en' => 'admin/tournaments/{slug}', 'ja' => 'admin/tonamento/{slug}', 'ko' => 'admin/toneomeonteu/{slug}', 'zh' => 'admin/jinbiaosai/{slug}', 'de' => 'admin/turniere/{slug}', 'fr' => 'admin/tournois/{slug}', 'es' => 'admin/torneos/{slug}', 'ru' => 'admin/turniry/{slug}'],
        'tournaments.cancel'   => ['vi' => 'admin/giai-dau/{slug}/huy', 'en' => 'admin/tournaments/{slug}/cancel', 'ja' => 'admin/tonamento/{slug}/kyanseru', 'ko' => 'admin/toneomeonteu/{slug}/chuiseo', 'zh' => 'admin/jinbiaosai/{slug}/quxiao', 'de' => 'admin/turniere/{slug}/absagen', 'fr' => 'admin/tournois/{slug}/annuler', 'es' => 'admin/torneos/{slug}/cancelar', 'ru' => 'admin/turniry/{slug}/otmenit'],
        'tournaments.destroy'  => ['vi' => 'admin/giai-dau/{slug}', 'en' => 'admin/tournaments/{slug}', 'ja' => 'admin/tonamento/{slug}', 'ko' => 'admin/toneomeonteu/{slug}', 'zh' => 'admin/jinbiaosai/{slug}', 'de' => 'admin/turniere/{slug}', 'fr' => 'admin/tournois/{slug}', 'es' => 'admin/torneos/{slug}', 'ru' => 'admin/turniry/{slug}'],

        // Profile Actions
        'change.password'        => ['vi' => 'doi-mat-khau', 'en' => 'change-password', 'ja' => 'pasuwado-henko', 'ko' => 'bimilbeonho-byeongyeong', 'zh' => 'genggaimima', 'de' => 'passwort-aendern', 'fr' => 'changer-mot-de-passe', 'es' => 'cambiar-contrasena', 'ru' => 'smenit-parol'],
        'change.name'            => ['vi' => 'doi-ten', 'en' => 'change-name', 'ja' => 'namae-henko', 'ko' => 'ileum-byeongyeong', 'zh' => 'genggaimingcheng', 'de' => 'name-aendern', 'fr' => 'changer-nom', 'es' => 'cambiar-nombre', 'ru' => 'smenit-imya'],
        'change.ui'              => ['vi' => 'doi-giao-dien', 'en' => 'change-ui', 'ja' => 'ui-henko', 'ko' => 'ui-byeongyeong', 'zh' => 'genggai-ui', 'de' => 'oberflaeche-aendern', 'fr' => 'changer-interface', 'es' => 'cambiar-interfaz', 'ru' => 'smenit-oformlenie'],
        'profile.picture.upload' => ['vi' => 'doi-anh-dai-dien', 'en' => 'upload-profile-picture', 'ja' => 'purofiru-gazo-appurodo', 'ko' => 'peulopil-sajin-eoblodeu', 'zh' => 'shangchuan-touxiang', 'de' => 'profilbild-hochladen', 'fr' => 'telecharger-photo-de-profil', 'es' => 'subir-foto-de-perfil', 'ru' => 'zagruzit-foto-profilya'],
        'profile.picture.remove' => ['vi' => 'xoa-anh-dai-dien', 'en' => 'remove-profile-picture', 'ja' => 'purofiru-gazo-sakujo', 'ko' => 'peulopil-sajin-sakje', 'zh' => 'shanchu-touxiang', 'de' => 'profilbild-entfernen', 'fr' => 'supprimer-photo-de-profil', 'es' => 'eliminar-foto-de-perfil', 'ru' => 'udalit-foto-profilya'],

        'article.index' => ['vi' => 'bai-viet', 'en' => 'articles', 'ja' => 'kiji', 'ko' => 'gisa', 'zh' => 'wenzhang', 'de' => 'artikel', 'fr' => 'article', 'es' => 'articulos', 'ru' => 'stati'],
        'article.show'  => ['vi' => 'bai-viet/{slug}', 'en' => 'articles/{slug}', 'ja' => 'kiji/{slug}', 'ko' => 'gisa/{slug}', 'zh' => 'wenzhang/{slug}', 'de' => 'artikel/{slug}', 'fr' => 'article/{slug}', 'es' => 'articulos/{slug}', 'ru' => 'stati/{slug}'],

        // Games (shared game library — CRUD is admin-only, these two are the
        // only public-facing pages for the games feature)
        'games.library' => ['vi' => 'thu-vien', 'en' => 'library', 'ja' => 'raiburari', 'ko' => 'raibeureoli', 'zh' => 'tushuguan', 'de' => 'bibliothek', 'fr' => 'bibliotheque', 'es' => 'biblioteca', 'ru' => 'biblioteka'],
        'games.show'    => ['vi' => 'van-co/{slug}', 'en' => 'games/{slug}', 'ja' => 'kifu/{slug}', 'ko' => 'gibo/{slug}', 'zh' => 'qipu/{slug}', 'de' => 'partien/{slug}', 'fr' => 'parties/{slug}', 'es' => 'partidas/{slug}', 'ru' => 'partii/{slug}'],
    ],
];
