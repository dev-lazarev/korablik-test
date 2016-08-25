git stash
git checkout -b develop
git stash pop
git add .
git commit -m 'change'
git push
git checkout master
git merge develop
git push