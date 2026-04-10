const fs = require('fs');

const text = `
            Office Furniture
        `;
const textNext = `
-
    Office Desks
`;

let regExp = /^-+/;

let treeText = text.trim();
let numHyphenMatch = treeText.match(regExp);
console.log("Parent match:", numHyphenMatch);

let treeTextNext = textNext.trim();
let numHyphenNextMatch = treeTextNext.match(regExp);
console.log("Child match:", numHyphenNextMatch);

// What about replace and trim?
treeTextNext = treeTextNext.replace(regExp, "").trim();
console.log("Child text after replace and trim:", JSON.stringify(treeTextNext));
