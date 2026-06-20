---
title: CV
permalink: resume.html
body_class: resume
intro: VCto & Architecte. J'aide les entreprises à définir leur vision produit, concevoir des architectures durables, et structurer leurs équipes techniques.
description: VCto & Architecte. Vision produit, architectures durables, équipes techniques.
---

<div class="resume-container" markdown="1">
<div class="resume-column" markdown="1">

## Curriculum vitæ

Je suis **VCto & Architect** : j'accompagne les entreprises dans la définition de leur vision produit, la conception d'architectures robustes et la mise en place de stratégies techniques durables.
Mon rôle consiste à aligner le produit, la technique et la valeur business, tout en apportant une expertise opérationnelle sur les choix d'infrastructure, de workflows et d'organisation des équipes tech.

### **Lugha, SaaS d'apprentissage des langues**

Je conçois **Lugha**, une plateforme complète dédiée au *language learning* :
chat conversationnel, moteur d'analyse, flashcards, SRS, suivi de progression, achievements, prononciation, objectifs quotidiens et intégration IA.
Lugha combine UX, linguistique et IA pour offrir une expérience d'apprentissage moderne et engageante.

### **Kemeter, PaaS & Infrastructure**

Je développe **Kemeter**, une plateforme PaaS pensée brique par brique et inspirée des cloud providers modernes :
virtualisation, provisioning, orchestration applicative, reverse-proxy haute performance, gestion des logs (Vector/Quickwit), monitoring, automatisation des déploiements, add-ons et modèles d'applications.
Kemeter est un projet d'architecture long terme, conçu pour être modulaire, performant et exploitable en production.

---

### **Développement & Expertise technique**

Je suis également **développeur confirmé** sur **PHP/Symfony, React et Rust**, avec une expérience complète des architectures web : API, front-end, back-end, CI/CD, conteneurisation Docker et optimisation.
J'intègre l'IA au cœur de mes workflows techniques, notamment via la mise en place d'**agents de code** capables d'automatiser des tâches, structurer les pipelines et accélérer la production.

**[Voir mon profil LinkedIn →](https://www.linkedin.com/in/mlanawo-mbechezi-53b5ab44/)**

</div>
<div class="resume-column" markdown="1">

## Bio

Une petite biographie prête à l'emploi :

<div class="bio-text" markdown="1">

Mlanawo Mbechezi est VCto & Architecte. Il aide les entreprises à définir leur vision produit, concevoir des architectures robustes, et mettre en place des stratégies techniques durables.

Il aligne produit, technique et valeur business, tout en apportant une expertise opérationnelle sur les choix d'infrastructure, les workflows et l'organisation des équipes tech.

Il travaille actuellement sur Lugha (un SaaS d'apprentissage des langues nouvelle génération) et Kemeter (une PaaS pensée brique par brique).

</div>

<button class="copy-button" data-copy-bio>Copier la bio</button>

<script>
document.querySelector('[data-copy-bio]')?.addEventListener('click', async (e) => {
  const btn = e.currentTarget;
  const text = document.querySelector('.bio-text')?.innerText.trim() ?? '';
  try {
    await navigator.clipboard.writeText(text);
    const original = btn.textContent;
    btn.textContent = 'Copié ✓';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = original; btn.classList.remove('copied'); }, 1800);
  } catch {
    btn.textContent = 'Échec';
  }
});
</script>

</div>
</div>
