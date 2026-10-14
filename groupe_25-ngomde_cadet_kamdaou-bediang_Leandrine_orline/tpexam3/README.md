# TP Exam 3 - Gestion d'une Ligne de Commande

Application Android de gestion des lignes de commande, developpee dans le cadre du TP Examen de Developpement Web - Master DI, UDM.

**Groupe 25** : Ngomde Cadet Kamdaou, Bediang Leandrine Orline

## Technologies

- **Langage** : Kotlin
- **UI** : Jetpack Compose + Material 3
- **Architecture** : MVVM + Clean Architecture (data / domain / presentation)
- **Base de donnees** : Room (SQLite)
- **Injection de dependances** : Koin
- **Chargement d'images** : Coil
- **Navigation** : Navigation Compose avec Bottom Navigation Bar

## Schema de la base de donnees

- `Client(idCli, nomCli, prenomCli, email, photo, idRegion)`
- `Commande(idCmd, dateCmd, #idCli)`
- `ligneCommande(#idCmd, #idProd, qteCmd)`
- `Produit(idProd, designProd, qteStock, prixUProd, photoProd)`
- `Region(idRegion, nomRegion, #idDep)`
- `Departement(idDep, nomDep)`

## Fonctionnalites

1. **Authentification** - Ecran de connexion avec identifiants pre-enregistres
2. **Gestion des Produits** - Liste avec recherche, ajout, modification, suppression
3. **Gestion des Commandes** - Liste des commandes, creation, ajout/suppression de produits dans une commande
4. **Liste des Clients** - Affichage des clients avec region et email
5. **Tableau de Bord** - Statistiques : chiffre d'affaires, top produits, revenus par region, commandes par client, performance mensuelle

## Identifiants de connexion

- **Login** : `MasTMound2`
- **Mot de passe** : `MoundMasT2@2026`

## Structure du projet

```
com.groupe25.tpexam3/
├── core/
│   ├── data/local/          # Database, Entities, DAOs
│   └── presentation/        # UiText (EN/FR)
├── di/                      # Koin modules
├── feature/
│   ├── auth/                # Authentification
│   ├── product/             # CRUD Produits
│   ├── order/               # Commandes + Lignes de commande
│   ├── client/              # Liste des clients
│   └── dashboard/           # Tableau de bord
├── navigation/              # Routes et Bottom Navigation
└── ui/theme/                # Theme Material 3
```

## Build

Ouvrir le projet dans Android Studio ou depuis le terminal :

```bash
./gradlew assembleDebug
```

L'APK sera genere dans `app/build/outputs/apk/debug/`.

## Langues supportees

- Anglais (par defaut)
- Francais
