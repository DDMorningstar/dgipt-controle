# Contrôle des Notes de Perception (DGIPT)

Application PWA pour vérifier les notes de perception, en ligne et hors ligne.

## Mise en ligne (Vercel + GitHub)

1. Créez une base **MySQL** (PlanetScale, Railway, Aiven, etc.).
2. Dans Vercel → Project → Settings → Environment Variables :
   - `DATABASE_URL` = `mysql://user:pass@host:3306/npc?ssl=true`
   - ou `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_SSL=true`
3. Déployez. L’URL HTTPS active le hors-ligne et l’installation sur téléphone.
4. Ouvrez l’app, connectez-vous (PIN `0000` admin), puis **Envoyer la base locale** depuis un appareil qui a déjà les notes, ou importez l’Excel.

## Local (WAMP, optionnel)

Les fichiers PHP à la racine restent utilisables avec MySQL local (`config.php`).
