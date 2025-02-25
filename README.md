# MyEasyEvent-back

| Code |        Message        |
| :--: | :-------------------: |
1000| Erreur de requête SQL
1100| Erreur Sendgrid
2000| Le service demandé -action- n'existe pas
||
session| 4950 - 4999
||
4950| L'id de l'utilisateur est manquant
4951| La dernière action est manquante
4953| Erreur lors de la création de la session
||
user| 5000 - 5079
||
5000| Adresse email utilisateur ne peut être null
5001| Adresse email utilisateur n'est pas valide
5002| Mot de passe trop court ( <8 )
5003| Pas de majuscule dans le mot de passe
5004| Pas de caractère spéciaux dans le mot de passe
5005| Le nom ET le prénom ne peuvent pas être vide
5007| Cette adresse email est déjà utilisée
5009| Ce token de device n'est pas valide! -> Utiliser login/pass et enregistrer + confirmer le device à nouveau
5011| Ce device n'est pas valide
5015| Ce device n'a pas encore été enregistré
5017| Le mot de passe ne correspond pas
5020| Erreur lors de la connexion avec login et mot de passe
5021| Erreur lors de la vérification du device
5022| Utilisateur non validé
5023| Les données de l'utilisateur ne sont pas valide
5024| Erreur lors de l'inscription
5025| Cet utilisateur n'existe pas 
||
event| 5080 - 5099
||
5080| Un user id est obligatoire
5081| Un titre est obligatoire
5082| Une description est obligatoire
5083| La selection d'un template est obligatoire
5084| Une date de début est obligatoire
5085| Une date de fin est obligatoire
5086| Une restriction d'age est obligatoire
5087| Un status de publication est obligatoire
||
authorizedDevice| 5100 - 5199
||
5100| Veuillez renseigner l'ID de l'utilisateur
5101| Veuillez renseigner la date de derniere utilisation
5102| Veuillez renseigner l'ID de l'appareil autorisé
||
email| 5200 - 5299
||
5200| Une seul adresse email d'expédition est requise
5201| Une adresse email d'expédition est requise
5202| Une adresse email de destination est requise
5203| Un sujet est requis
5204| Un message est requis
5205| Adresse email invalide
5206| Un nom de destinataire est requis
||
reservation| 5300 - 5399
||
5300| Veuillez renseigner le prénom et le prénom
5301| Veuillez renseigner l'email
5302| Veuillez renseigner un email valide
5303| Une reservation avec cet email existe deja
5304| Veuillez renseigner votre age
5305| Vous n'avez pas l'age requis pour cet évement
||
catégories| 5400 - 5499
||
5400| Veuillez renseigner le nom de la categorie

---

/**
 * Libraries
 */
/**
 * Factories
 */
/**
 * Repositories
 */
/**
 * Validators
 */
/**
 * Services
 */