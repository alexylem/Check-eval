# Check Eval

lightweight php script to quickly check if there infected php file infected by malicious code such as eval.
Script is searching recursively across sub-directories from folder where it is placed.
Outputs list of potentially infected files if any along with 301 http error code.
Best used with cron.

```shell
$> curl -fsS "https://myside.com/check_eval.php" && echo "no infected files found" || echo "infected files found"
```

### License

[![License-shield]](LICENSE.md)
