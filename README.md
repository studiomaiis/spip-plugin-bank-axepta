# Passerelle de paiement Axepta pour SPIP

Accepter les paiements par carte via la passerelle Axepta lorsque vous utilisez le [plugin Bank](https://contrib.spip.net/Plugin-Bank). Ce module redirige le client vers l'environnement sécurisé Axepta et propose le paiement par carte (paySSL). Ce plugin SPIP ne propose que les paiements par cartes bancaires.

## Configuration requise

* PHP : 7.4 et plus
* SPIP : 4.0 et plus
* Un compte Axepta - BNP Paribas avec un environnement 3DS2 acceptant les appels à *Platform HTML Forms* (paySSL)

Il se peut que le plugin fonctionne sur des versions antérieures de PHP, SPIP ou Bank, mais il n'a pas été testé dans d'autres conditions que celles mentionnées ci-dessus.

## Installation & mises à jour

Ajoutez un nouveau dépôt, l'URL à ajouter dans le champ *Fichier XML du dépôt* est `https://depot.studiomaiis.net/spip.xml`, puis cherchez le plugin `bank_axepta` et installez-le.

Vous devrez ajouter ces instructions à votre `.htaccess` :

```
RewriteCond %{QUERY_STRING} ^(.*)$
RewriteRule ^axepta/(.*)/(.*)/(.*)/$ spip.php?action=bank_$2&bankp=axepta-$1&id=$3&%1 [L]
```

Sans quoi les notifications de la banque n'aboutiront pas.

## Questions fréquentes

### A qui s'adresse ce plugin ?

Aux développeurs et aux intégrateurs.

### Ce plugin supporte-t'il les remboursements ?

Non.

### Ce plugin supporte-t'il les paiements récurrents, les abonnements ?

Non.

### Est-ce qu'un certificat SSL est nécessaire ?

Non, ce n'est pas nécessaire, mais c'est hautement recommandé pour ajouter un niveau supplémentaire de sécurité pour vos clients. Techniquement, à la validation d'une commande, les utilisateurs sont redirigés vers les serveurs de la banque via une requête HTTPS.

### Ce plugin supporte-til les environnements de test et de production ?

Oui, vous pouvez ajouter plusieurs prestataires *Axepta* avec le plugin *Bank* : un pour les tests, et un pour la prod. Les valeurs par défaut correspondent à l'environnement de test générique *Axepta*, à noter que les identifiants de votre environnement de test dédié (dont le MID se termine par `_t`) ne fonctionneront pas car cet environnement ne supporte pas le 3DS2.

![Ajout d'un prestataire Axepta dans l'admin de SPIP](https://depot.studiomaiis.net/screenshots/bank_axepta_ajout_presta.png "Ajout d'un prestataire Axepta dans l'admin de SPIP")

![Configuration de la passerelle Axepta dans l'admin de SPIP](https://depot.studiomaiis.net/screenshots/bank_axepta_config.png "Configuration de la passerelle Axepta dans l'admin de SPIP")

Pendant vos développements vous pourrez cocher la case *Visible par les admins seulement* pour éviter que d'autres personnes que les administrateurs connectés ne puissent utiliser la passerelle.

Si vous avez perdu les identifiants de test générique, vous pouvez les retrouver [ici](https://docs.axepta.bnpparibas/display/DOCBNP/3DSV2+Test+environment).

### Quels sont les types de paiement proposés ?

Seuls les paiements par carte bancaire sont supportés, les types de paiement comme PayPal, etc... ne sont pas supportés.

### Comment puis-je configurer ce module de paiement ?

Si vous avez le MerchantID (ou MID dans la doc), la clé HMAC et la clé Blowfish, vous avez tout ce qu'il vous faut et vous n'aurez aucun mal à les saisir aux endroits réservés !

Veuillez vous référer à la [documentation officielle](https://docs.axepta.bnpparibas/display/DOCBNP/Premiers+pas+avec+AXEPTA+BNP+Paribas) pour récupérer vos identifiants.

Attention à ne pas utiliser les tickets Github pour des demandes de support.

### Sur l'environnement de test, où puis-je trouver les cartes de test ?

Les cartes de test peuvent être trouvées [ici](https://docs.axepta.bnpparibas/display/DOCBNP/3DSV2+Test+environment).

### Un support, une aide sont-ils proposés pour récupérer les informations MerchantId, clés HMAC et Blowfish ?

Non, ce plugin s'adresse plutôt à des développeurs et intégrateur qui savent récupérer ces informations. Veuillez vous référer à la [documentation officielle](https://docs.axepta.bnpparibas/display/DOCBNP/Premiers+pas+avec+AXEPTA+BNP+Paribas) ou contacter le support Axepta.

Si vraiment vous ne savez pas faire, vous pouvez m'envoyer une demande sur mon [site internet](https://www.studiomaiis.net), toute intervention fera l'objet d'un devis et d'une facture. Sachez que récupérer ces informations peut s'avérer assez chronophage.

### Comment passe-t'on en mode production ?

Lorsque vous avez récupéré vos identifiants de production MerchantID, les clés HMAC et Blowfish, saisissez-les dans la configuration du prestataire et activez-le.

### J'ai trouvé un bug, comment puis-je le faire remonter ?

Si c'est un bug (pas une demande de support) :
1. Vérifiez que vous avez la configuration requise : PHP, SPIP et le plugin Bank,
2. Vérifiez que votre serveur est accessible - exemple : si vous développez en local, les serveurs de la banque ne pourront pas envoyer leurs réponses à votre serveur,
3. Regardez vos logs `tmp/logs/bank.log`, `tmp/logs/bank_response.log` et `tmp/logs/axepta.log`,
4. Désactivez les plugins qui pourraient interférer,
5. Contactez le support Axepta, ils peuvent avoir des informations additionnelles, surtout s'il s'agit de votre environnement de production,
6. Soumettez un bug sur [GitHub](https://github.com/studiomaiis/spip-plugin-bank-axepta/issues).

S'il s'agit d'une demande de support, vous pouvez m'envoyer une demande sur mon [site internet](https://www.studiomaiis.net), toute intervention fera l'objet d'un devis et d'une facture. 

