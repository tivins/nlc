
test:
	./runtest.php -rdf data

clean:
	$(RM) -r docs/api/generated

doc:
	./review.php

install:
	cp -f nlang.php /usr/local/bin/nlc
	cp -f runtest.php /usr/local/bin/nltest

uninstall:
	$(RM) /usr/local/bin/nlc
	$(RM) /usr/local/bin/nltest