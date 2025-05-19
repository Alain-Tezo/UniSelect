@component('mail::message')
# Félicitations {{ $etudiant->prenom }} {{ $etudiant->nom }} !

Nous avons le plaisir de vous informer que vous avez été sélectionné(e) pour poursuivre vos études dans notre université.

## Détails de votre sélection
- **Niveau** : {{ $niveau->nom }}
- **Filière attribuée** : {{ $filiere->nom }}
@if($filiere->est_selective)
- **Points obtenus** : {{ number_format($etudiant->points_selection, 2) }}
@endif

## Prochaines étapes
Veuillez vous présenter au service des inscriptions de l'université avec les documents suivants :
1. Votre carte d'identité nationale
2. Vos relevés de notes
3. Votre diplôme ou attestation de réussite
4. 2 photos d'identité récentes

La période d'inscription est du **{{ date('d/m/Y', strtotime('+1 week')) }}** au **{{ date('d/m/Y', strtotime('+3 weeks')) }}**.

@component('mail::button', ['url' => config('app.url')])
Accéder au site de l'université
@endcomponent

Pour toute question ou information complémentaire, n'hésitez pas à contacter le service des inscriptions :
- Par téléphone : +237 659 65 31 53

Cordialement,<br>
{{ config('app.name') }}

<small>Ceci est un email automatique, merci de ne pas y répondre.</small>
@endcomponent
