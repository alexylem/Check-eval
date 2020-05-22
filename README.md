# Check Eval

lightweight php script to search & remove php files infected by malicious code such as eval.
Script is searching recursively across sub-directories from folder where it is placed.
Outputs list of potentially infected files with option to select which one to delete.
Exits with 301 http error code is infected file found for use with cron.

```shell
$> curl -fsS "https://myside.com/check_eval.php" && echo "no infected files found" || echo "infected files found"
```
