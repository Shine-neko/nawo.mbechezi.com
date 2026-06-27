---
title: Resume
permalink: resume.html
body_class: resume
intro: VCto & Architect, building product vision, sustainable architectures, and engineering teams.
description: VCto & Architect, building product vision, sustainable architectures, and engineering teams.
---

<div class="resume-container" markdown="1">
<div class="resume-column" markdown="1">

## Curriculum

I'm a **VCto & Architect**: I help companies define their product vision, design robust architectures, and put sustainable technical strategies in place.
My role is to align product, engineering and business value, while bringing hands-on expertise on infrastructure choices, workflows and tech team organization.

### **Lugha, Language learning SaaS**

I'm building **Lugha**, an end-to-end platform dedicated to *language learning*:
conversational chat, analysis engine, flashcards, SRS, progression tracking, achievements, pronunciation, daily goals and AI integration.
Lugha combines UX, linguistics and AI to deliver a modern, engaging learning experience.

### **Kemeter, PaaS & Infrastructure**

I'm building **Kemeter**, a PaaS designed brick by brick, inspired by modern cloud providers:
virtualization, provisioning, application orchestration, high-performance reverse-proxy, log management, monitoring, deployment automation, add-ons and application templates.
Kemeter is a long-term architecture project modular, performant, production-grade.

---

### **Engineering & Technical expertise**

I'm also a **seasoned developer** working with **PHP/Symfony, React and Rust**, with end-to-end experience in web architectures: APIs, front-end, back-end, CI/CD, Docker containerization and performance optimization.
I bake AI into my technical workflows notably by deploying **code agents** that automate tasks, structure pipelines and accelerate delivery.

**[View my LinkedIn Profile →](https://www.linkedin.com/in/mlanawo-mbechezi-53b5ab44/)**

</div>

<button class="copy-button" data-copy-bio>Copy bio</button>

<script>
document.querySelector('[data-copy-bio]')?.addEventListener('click', async (e) => {
  const btn = e.currentTarget;
  const text = document.querySelector('.bio-text')?.innerText.trim() ?? '';
  try {
    await navigator.clipboard.writeText(text);
    const original = btn.textContent;
    btn.textContent = 'Copied ✓';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = original; btn.classList.remove('copied'); }, 1800);
  } catch {
    btn.textContent = 'Copy failed';
  }
});
</script>

</div>
</div>
