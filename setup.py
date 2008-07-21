from distutils.core import setup
import os

def to_include(search='.'):
    """
    to_include(search='.') -> tuple(packages, files)
    Generate a list of directory ``search`` packages and data files 
    to include in a distutils setup. "Borrowed" from Django's
    distutils setup.
    
    """
    packages, data = [], []
    for path, dirs, files in os.walk(search):
        for directory in dirs:
            if directory.startswith('.'):
                del dirs[dirs.index(directory)]

        if '__init__.py' in files:
            packages.append(path.replace('/', '.'))
        else:
            data.append((path, [os.path.join(path, item) for item in files]))

    return packages, data

packages, data = to_include('visicon')
setup(
    name='visicon',
    version='0.1',
    description='IP address visualisation',
    author='Antisense',
    author_email='veracon@gmail.com',
    url='http://code.google.com/p/visicon',
    packages=packages,
    data_files=data
)
