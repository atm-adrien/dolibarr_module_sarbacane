
# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]



Version 1.1 (2021-06-09)
=====================
- FIX : Mise à jour des stats : toujours renseigner "used_blacklist" *24/08/2021* - 1.1.22
- FIX : Récupération de l'ID de la blacklist de la campagne *17/08/2021* - 1.1.21
- FIX : Lorsque l'on ajoute des destinataires à une campagne Sarbacane via une liste de diffusion Dolibarr, les destinataires n'étaient pas inscrits en BDD *16/08/2021* - 1.1.20
- FIX : La liste de désinscription liée à la campagne n'est plus systématiquement renseignée avec la liste par défaut mais doit forcément être renseignée manuellement pour pouvoir créer la campagne Sarbacane *26/07/2021* - 1.1.19
- FIX Changement nom fonction "getNPAIContact" en "getNPAIContactEmail" + cette fonction peut désormais retourner la liste des contacts dolibarr ayant l'extrafield "sarb_npai" coché *2021-07-13* - 1.1.18
- FIX Trad "SarbacaneDestList" au lieu de "SarbacaneList"  *2021-07-02* - 1.1.17
- Statut "Erreur" contacts NPAI lors de la màj des stats *2021-07-02* - 1.1.16
- Ajout fonctions : getNPAIContact() && getBlacklistedContact() *2021-07-02* - 1.1.15
- FIX blacklist select : ajout d'une valeur empty *2021-06-30* - 1.1.14
- FIX Mise à jour des statistiques : si npai, alors la campagne doit être envoyé partiellement *2021-06-30* - 1.1.13
- FIX contact_tab.php : on n'affiche que les campagnes au statut "envoyé " et "envoyé partiellement" *2021-06-25* - 1.1.12
- FIX contact_tab.php fatal error *2021-06-25* - 1.1.11
- FIX missing try catch *2021-06-24* - 1.1.10
- FIX Mailing Delete Error Management *2021-06-24* - 1.1.9
- FIX Mise à jour des statistique = Mise à jour du statut du mailing dolibarr *2021-06-23* - 1.1.8
- FIX Distribution List Compatibility *2021-06-22* - 1.1.7
- FIX delete sarbacane campaign and stats when mailing is deleting *2021-06-17* - 1.1.6
- FIX Set blacklist_id by "DEFAULT_BLACKLIST" when it is empty *2021-06-16* - 1.1.5
- FIX Modify module position to place it under Emailing module in perms list *2021-06-02* - 1.1.4
- NEW (describe any new feature that is only included in the main branch but not
  yet in release branches) *2021-06-01* - 1.1.3
- FIX Update campaign stats for the last three months only *2021-06-01* - 1.1.2
- FIX : Màj statistiques => statuts des destinataires du mailing même si il n'y a pas de destinataires de type contact - *15/06/2021* - 1.1.1
- NEW : Ajout conf "SARBACANE_EXPORT_EMPTYLIST" qui permet de vider totalement la liste de contacts avant l'ajout lors de l'export - *09/06/2021* - 1.1

## Version 1.0
- NEW init sarbacane *2021-04-28* - 1.0

